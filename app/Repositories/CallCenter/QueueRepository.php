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

    public function getPackagesForReturn(string $token): JsonResponse
    {
        // Get the token
        $tokenModel = \App\Models\Token::where('token', $token)->first();
        
        if (! $tokenModel) {
            return response()->json(['error' => 'Token not found'], 404);
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

        // Get individual HBL packages - only show HELD packages (not released)
        $hblPackages = $hbl->packages()
            ->withoutGlobalScopes()
            ->where('release_status', 'held') // Only held packages can be returned to bond
            ->get();

        if ($hblPackages->isEmpty()) {
            return response()->json(['error' => 'No held packages found for this token'], 404);
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
