<?php

namespace App\Repositories\CallCenter;

use App\Actions\Queue\QueueCount;
use App\Http\Resources\CallCenter\CustomerQueueResource;
use App\Http\Resources\CallCenter\PackageQueueResource;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\PackageQueue;
use App\Models\PackageReleaseLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueRepository implements QueueRepositoryInterface
{
    public function getReceptionQueue(): JsonResponse
    {
        $queue = CustomerQueue::receptionQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getDocumentVerificationQueue(): JsonResponse
    {
        $queue = CustomerQueue::documentVerificationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getReceptionQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::receptionQueue();

        return QueueCount::run($queue);
    }

    public function getDocumentVerificationQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::documentVerificationQueue();

        return QueueCount::run($queue);
    }

    public function getCashierQueue(): JsonResponse
    {
        $queue = CustomerQueue::cashierQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getCashierQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::cashierQueue();

        return QueueCount::run($queue);
    }

    public function getExaminationQueue(): JsonResponse
    {
        $queue = CustomerQueue::examinationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getExaminationQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::examinationQueue();

        return QueueCount::run($queue);
    }

    public function getPackageQueue(): JsonResponse
    {
        $queue = PackageQueue::whereHas('token')->get();

        $packageQueues = PackageQueueResource::collection($queue);

        return response()->json($packageQueues, 200);
    }

    public function getPackageDetailsByToken(string $token): JsonResponse
    {
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($token) {
            $query->where('token', $token);
        })->with(['token.customer', 'releasedBy'])->first();

        if (! $packageQueue) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json([
            'reference' => $packageQueue->reference,
            'customer' => $packageQueue->token->customer->name,
            'package_count' => $packageQueue->package_count,
            'released_at' => $packageQueue->released_at,
            'released_packages' => $packageQueue->released_packages,
        ]);
    }

    public function getPackagesForReturn(string $input): JsonResponse
    {
        // Determine if input is a token number or HBL reference
        // Token numbers are typically numeric, HBL references contain letters/dashes
        $isTokenNumber = is_numeric($input);

        if ($isTokenNumber) {
            return $this->getPackagesByToken($input);
        } else {
            return $this->getPackagesByHBL($input);
        }
    }

    private function getPackagesByToken(string $token): JsonResponse
    {
        // Get the token
        $tokenModel = \App\Models\Token::where('token', $token)->first();

        if (! $tokenModel) {
            // Log invalid token search attempt
            \Log::warning('Invalid token search attempt', [
                'token' => $token,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'reason' => 'Token not found',
            ]);

            return response()->json(['error' => 'Token not found'], 404);
        }

        // VALIDATION 1: Token must be created today
        if (! $tokenModel->created_at->isToday()) {
            // Log invalid token date attempt
            \Log::warning('Invalid token date search attempt', [
                'token' => $token,
                'token_id' => $tokenModel->id,
                'token_date' => $tokenModel->created_at->format('Y-m-d'),
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'reason' => 'Token not from today',
            ]);

            return response()->json([
                'error' => 'Invalid token: Token must be from today',
                'details' => 'Token was created on ' . $tokenModel->created_at->format('Y-m-d') . '. Only tokens created today can be processed.',
                'token_date' => $tokenModel->created_at->format('Y-m-d'),
            ], 422);
        }

        // VALIDATION 2: Token must be in Examination Queue
        $examinationQueue = CustomerQueue::where('token_id', $tokenModel->id)
            ->where('type', CustomerQueue::EXAMINATION_QUEUE)
            ->whereNull('left_at') // Must still be active in the queue
            ->first();

        if (! $examinationQueue) {
            // Log invalid queue status attempt
            \Log::warning('Invalid token queue status search attempt', [
                'token' => $token,
                'token_id' => $tokenModel->id,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'reason' => 'Token not in Examination Queue',
            ]);

            return response()->json([
                'error' => 'Invalid token: Token is not in Examination Queue',
                'details' => 'This token is not currently in the Examination Queue. Only tokens in the Examination Queue can have packages returned.',
            ], 422);
        }

        // Get the HBL for this token
        $hbl = \App\Models\HBL::withoutGlobalScopes()
            ->where('reference', $tokenModel->reference)
            ->first();

        if (! $hbl) {
            return response()->json(['error' => 'HBL not found for this token'], 404);
        }

        // Get package queue for this token
        $packageQueue = PackageQueue::where('token_id', $tokenModel->id)->first();

        // VALIDATION 3: Package queue must exist for tokens in examination queue
        if (! $packageQueue) {
            return response()->json([
                'error' => 'No package queue found for this token',
                'details' => 'This token does not have an associated package queue.',
            ], 404);
        }

        // Get individual HBL packages - only show RELEASED packages (released from bond to examination)
        $hblPackages = $hbl->packages()
            ->withoutGlobalScopes()
            ->where('release_status', 'released') // Packages released from bond to examination
            ->get();

        if ($hblPackages->isEmpty()) {
            return response()->json([
                'error' => 'No packages available for return',
                'details' => 'All packages for this token are either still in bond storage, already examined, or returned to bond.',
            ], 404);
        }

        // Build individual package list
        $individualPackages = $hblPackages->map(function ($package) use ($packageQueue, $hbl) {
            return [
                'id' => $package->id,
                'hbl_package_id' => $package->id,
                'package_queue_id' => $packageQueue ? $packageQueue->id : null,
                'hbl_reference' => $hbl->reference,
                'hbl_number' => $hbl->hbl_number,
                'package_type' => $package->package_type,
                'quantity' => $package->quantity,
                'length' => $package->length,
                'width' => $package->width,
                'height' => $package->height,
                'weight' => $package->weight,
                'volume' => $package->volume,
                'release_status' => $package->release_status,
                'is_released' => false, // These are held packages
                'released_at' => null,
                'bond_storage_number' => $package->bond_storage_number,
                'remarks' => $package->remarks,
            ];
        })->values()->all();

        return response()->json([
            'token_number' => $token,
            'customer' => $tokenModel->customer->name ?? 'Unknown',
            'hbl_reference' => $hbl->reference,
            'hbl_number' => $hbl->hbl_number,
            'individual_packages' => $individualPackages,
            'summary' => [
                'total_packages' => $hbl->packages()->withoutGlobalScopes()->count(),
                'held_packages' => count($individualPackages),
                'available_for_return' => count($individualPackages),
            ],
        ]);
    }

    private function getPackagesByHBL(string $hblReference): JsonResponse
    {
        // Get the HBL
        $hbl = \App\Models\HBL::withoutGlobalScopes()
            ->where('reference', $hblReference)
            ->orWhere('hbl_number', $hblReference)
            ->first();

        if (! $hbl) {
            // Log invalid HBL search attempt
            \Log::warning('Invalid HBL search attempt', [
                'hbl_reference' => $hblReference,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'reason' => 'HBL not found',
            ]);

            return response()->json(['error' => 'HBL not found'], 404);
        }

        // Get the token for this HBL
        $tokenModel = \App\Models\Token::where('reference', $hbl->reference)->first();

        if (! $tokenModel) {
            return response()->json([
                'error' => 'No token found for this HBL',
                'details' => 'This HBL does not have an associated token.',
            ], 404);
        }

        // VALIDATION 1: Token must be created today
//        if (! $tokenModel->created_at->isToday()) {
//            // Log invalid token date attempt
//            \Log::warning('Invalid HBL token date search attempt', [
//                'hbl_reference' => $hblReference,
//                'token' => $tokenModel->token,
//                'token_id' => $tokenModel->id,
//                'token_date' => $tokenModel->created_at->format('Y-m-d'),
//                'user_id' => auth()->id(),
//                'ip' => request()->ip(),
//                'reason' => 'Token not from today',
//            ]);
//
//            return response()->json([
//                'error' => 'Invalid HBL: Associated token must be from today',
//                'details' => 'The token for this HBL was created on ' . $tokenModel->created_at->format('Y-m-d') . '. Only HBLs with tokens created today can be processed.',
//                'token_date' => $tokenModel->created_at->format('Y-m-d'),
//                'token_number' => $tokenModel->token,
//            ], 422);
//        }

        // VALIDATION 2: Token must be in Examination Queue
        $examinationQueue = CustomerQueue::where('token_id', $tokenModel->id)
            ->where('type', CustomerQueue::EXAMINATION_QUEUE)
            ->whereNull('left_at')
            ->first();

        if (! $examinationQueue) {
            // Log invalid queue status attempt
            \Log::warning('Invalid HBL queue status search attempt', [
                'hbl_reference' => $hblReference,
                'token' => $tokenModel->token,
                'token_id' => $tokenModel->id,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'reason' => 'Token not in Examination Queue',
            ]);

            return response()->json([
                'error' => 'Invalid HBL: Associated token is not in Examination Queue',
                'details' => 'The token for this HBL is not currently in the Examination Queue. Only HBLs with tokens in the Examination Queue can have packages returned.',
                'token_number' => $tokenModel->token,
            ], 422);
        }

        // Get package queue
        $packageQueue = PackageQueue::where('token_id', $tokenModel->id)->first();

        if (! $packageQueue) {
            return response()->json([
                'error' => 'No package queue found for this HBL',
                'details' => 'This HBL does not have an associated package queue.',
            ], 404);
        }

        // Get individual HBL packages - only show RELEASED packages (released from bond to examination)
        $hblPackages = $hbl->packages()
            ->withoutGlobalScopes()
            ->where('release_status', 'released') // Packages released from bond to examination
            ->get();

        if ($hblPackages->isEmpty()) {
            return response()->json([
                'error' => 'No packages available for return',
                'details' => 'All packages for this HBL are either still in bond storage, already examined, or returned to bond.',
            ], 404);
        }

        // Build individual package list
        $individualPackages = $hblPackages->map(function ($package) use ($packageQueue, $hbl) {
            return [
                'id' => $package->id,
                'hbl_package_id' => $package->id,
                'package_queue_id' => $packageQueue ? $packageQueue->id : null,
                'hbl_reference' => $hbl->reference,
                'hbl_number' => $hbl->hbl_number,
                'package_type' => $package->package_type,
                'quantity' => $package->quantity,
                'length' => $package->length,
                'width' => $package->width,
                'height' => $package->height,
                'weight' => $package->weight,
                'volume' => $package->volume,
                'release_status' => $package->release_status,
                'is_released' => false,
                'released_at' => null,
                'bond_storage_number' => $package->bond_storage_number,
                'remarks' => $package->remarks,
            ];
        })->values()->all();

        return response()->json([
            'token_number' => $tokenModel->token,
            'customer' => $tokenModel->customer->name ?? 'Unknown',
            'hbl_reference' => $hbl->reference,
            'hbl_number' => $hbl->hbl_number,
            'individual_packages' => $individualPackages,
            'summary' => [
                'total_packages' => $hbl->packages()->withoutGlobalScopes()->count(),
                'held_packages' => count($individualPackages),
                'available_for_return' => count($individualPackages),
            ],
        ]);
    }

    public function returnPackage(array $data): void
    {
        // Handle selective package returns (individual HBLPackage records)
        if (isset($data['selected_packages']) && ! empty($data['selected_packages'])) {
            DB::beginTransaction();

            try {
                // Get the individual package IDs that were selected
                $selectedPackageIds = collect($data['selected_packages'])
                    ->pluck('hbl_package_id')
                    ->filter()
                    ->values()
                    ->all();

                // Get package details for logging
                $selectedPackages = \App\Models\HBLPackage::withoutGlobalScopes()
                    ->whereIn('id', $selectedPackageIds)
                    ->get();

                $packageQueueId = null;
                $tokenId = null;

                // Process each selected package
                foreach ($selectedPackages as $package) {
                    // Update package status to returned_to_bond
                    $package->update([
                        'release_status' => 'returned_to_bond',
                        'release_note' => $data['remarks'] ?? 'Returned to bond storage',
                    ]);

                    // Get package queue and token info
                    if (!$packageQueueId) {
                        $hbl = $package->hbl;
                        $token = \App\Models\Token::where('reference', $hbl->reference)->first();
                        if ($token) {
                            $tokenId = $token->id;
                            $packageQueue = PackageQueue::where('token_id', $token->id)->first();
                            $packageQueueId = $packageQueue ? $packageQueue->id : null;
                        }
                    }

                    // Create package examination record for audit trail
                    if ($tokenId) {
                        \App\Models\PackageExamination::create([
                            'hbl_package_id' => $package->id,
                            'examination_id' => null, // No examination record for returns from bond
                            'customer_queue_id' => null,
                            'token_id' => $tokenId,
                            'action' => 'returned_to_bond',
                            'note' => $data['remarks'] ?? 'Returned to bond storage',
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);
                    }
                }

                // Build package details for log
                $returnedPackages = $selectedPackages->map(function ($pkg) {
                    return [
                        'hbl_package_id' => $pkg->id,
                        'package_type' => $pkg->package_type,
                        'quantity' => $pkg->quantity,
                        'size' => "{$pkg->length}x{$pkg->width}x{$pkg->height}",
                        'weight' => $pkg->weight,
                        'volume' => $pkg->volume,
                        'returned_at' => now()->toDateTimeString(),
                    ];
                })->values()->all();

                // Create a release log entry for the return
                if ($packageQueueId) {
                    PackageReleaseLog::create([
                        'package_queue_id' => $packageQueueId,
                        'type' => 'return',
                        'packages' => $returnedPackages,
                        'remarks' => $data['remarks'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    // Update package queue counts
                    $packageQueue = PackageQueue::find($packageQueueId);
                    if ($packageQueue) {
                        $returnedCount = count($selectedPackageIds);
                        $packageQueue->update([
                            'held_package_count' => max(0, $packageQueue->held_package_count - $returnedCount),
                        ]);
                    }
                    
                    // Check if all packages have been returned to bond
                    if ($tokenId) {
                        $token = \App\Models\Token::find($tokenId);
                        if ($token) {
                            $hbl = \App\Models\HBL::withoutGlobalScopes()
                                ->where('reference', $token->reference)
                                ->first();
                            
                            if ($hbl) {
                                // Count packages that are still in examination (status = 'released')
                                $remainingInExamination = $hbl->packages()
                                    ->withoutGlobalScopes()
                                    ->where('release_status', 'released')
                                    ->count();
                                
                                // If no packages remain in examination, move token back to Package Queue (Bond Area)
                                if ($remainingInExamination === 0) {
                                    // Remove from examination queue
                                    $examinationQueue = CustomerQueue::where('token_id', $tokenId)
                                        ->where('type', CustomerQueue::EXAMINATION_QUEUE)
                                        ->whereNull('left_at')
                                        ->first();
                                    
                                    if ($examinationQueue) {
                                        $examinationQueue->update([
                                            'left_at' => now(),
                                        ]);
                                        
                                        // Add queue status log for leaving examination
                                        $examinationQueue->addQueueStatus(
                                            CustomerQueue::EXAMINATION_QUEUE,
                                            $token->customer_id,
                                            $tokenId,
                                            null, // arrival_at (not changing)
                                            now()->toDateTimeString() // left_at
                                        );
                                    }
                                    
                                    // Create/reactivate Package Queue (Bond Area Queue)
                                    $bondAreaQueue = CustomerQueue::where('token_id', $tokenId)
                                        ->where('type', CustomerQueue::BOND_AREA_QUEUE)
                                        ->first();
                                    
                                    if ($bondAreaQueue) {
                                        // Reactivate existing bond area queue
                                        $bondAreaQueue->update([
                                            'left_at' => null,
                                            'arrived_at' => now(),
                                        ]);
                                    } else {
                                        // Create new bond area queue
                                        $bondAreaQueue = CustomerQueue::create([
                                            'type' => CustomerQueue::BOND_AREA_QUEUE,
                                            'token_id' => $tokenId,
                                            'arrived_at' => now(),
                                        ]);
                                    }
                                    
                                    // Add queue status log for entering bond area
                                    $bondAreaQueue->addQueueStatus(
                                        CustomerQueue::BOND_AREA_QUEUE,
                                        $token->customer_id,
                                        $tokenId,
                                        now()->toDateTimeString(), // arrival_at
                                        null // left_at (not leaving)
                                    );
                                    
                                    // Update package queue status
                                    if ($packageQueue) {
                                        $packageQueue->update([
                                            'is_released' => false,
                                            'status' => 'pending',
                                            'completed_at' => null,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception('Failed to return packages to bond: ' . $e->getMessage());
            }

            return;
        }

        // Legacy single package return (maintain backward compatibility)
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($data) {
            $query->where('token', $data['token_number']);
        })->first();

        if (! $packageQueue) {
            return; // Simply stop execution if not found
        }

        // Store the released packages before clearing them
        $releasedPackages = $packageQueue->released_packages ?? [];

        // Ensure we have valid package data
        if (empty($releasedPackages)) {
            $releasedPackages = [
                [
                    'reference' => $packageQueue->reference,
                    'package_count' => $packageQueue->package_count,
                    'returned_at' => now()->toDateTimeString(),
                ],
            ];
        }

        // Create a release log entry for the return
        PackageReleaseLog::create([
            'package_queue_id' => $packageQueue->id,
            'type' => 'return',
            'packages' => $releasedPackages,
            'remarks' => $data['remarks'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update the package queue to mark as not released
        $packageQueue->update([
            'is_released' => false,
            'released_at' => null,
            'released_packages' => null,
            'note' => $data['remarks'] ?? null,
            'auth_id' => auth()->id(),
        ]);
    }

    public function getPackageLogs(int $packageQueueId): JsonResponse
    {
        $logs = PackageReleaseLog::where('package_queue_id', $packageQueueId)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
