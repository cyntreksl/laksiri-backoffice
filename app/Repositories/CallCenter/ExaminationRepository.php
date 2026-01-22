<?php

namespace App\Repositories\CallCenter;

use App\Actions\CustomerFeedback\SendFeedbackMail;
use App\Actions\Examination\CreateExamination;
use App\Http\Resources\CallCenter\ExaminationCollection;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\PackageExamination;
use App\Models\PackageQueue;
use Illuminate\Support\Facades\DB;

class ExaminationRepository implements ExaminationRepositoryInterface
{
    public function getPackagesForExamination(string $reference)
    {
        $hbl = HBL::withoutGlobalScopes()->where('reference', $reference)->first();

        if (!$hbl) {
            return response()->json(['error' => 'HBL not found'], 404);
        }

        // Get packages that are:
        // 1. Released from bonded area (release_status = 'released')
        // 2. Not yet released from examination (check package_examinations table)
        $packages = $hbl->packages()
            ->where('release_status', 'released')
            ->with(['packageExaminations' => function ($query) {
                $query->latest();
            }])
            ->get()
            ->filter(function ($package) {
                // Only show packages that haven't been released from examination
                $latestExamination = $package->packageExaminations->first();
                return !$latestExamination || $latestExamination->action !== 'released';
            })
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'package_type' => $package->package_type,
                    'quantity' => $package->quantity,
                    'length' => $package->length,
                    'width' => $package->width,
                    'height' => $package->height,
                    'weight' => $package->weight,
                    'volume' => $package->volume,
                    'release_status' => $package->release_status,
                    'released_at' => $package->released_at?->format('Y-m-d H:i:s'),
                    'released_by' => $package->releasedByUser?->name,
                    'bond_storage_number' => $package->bond_storage_number,
                    'remarks' => $package->remarks,
                ];
            })
            ->values();

        return response()->json($packages);
    }

    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();

            // Get customer queue ID
            $customerQueueId = $data['customer_queue_id'] ?? null;

            if (!$customerQueueId) {
                throw new \Exception('Customer queue ID is missing.');
            }

            $customerQueue = CustomerQueue::with('token')->find($customerQueueId);
            
            if (!$customerQueue) {
                throw new \Exception('Customer queue not found.');
            }

            $reference = $customerQueue->token->reference;

            $hbl = HBL::withoutGlobalScopes()->where('reference', $reference)->first();

            if (!$hbl) {
                throw new \Exception('HBL not found.');
            }

            // Prepare data for CreateExamination action (it expects customer_queue structure)
            $examinationData = [
                'customer_queue' => [
                    'id' => $customerQueue->id,
                    'token_id' => $customerQueue->token_id,
                ],
                'released_packages' => $data['released_packages'] ?? [],
                'note' => $data['note'] ?? null,
            ];

            // Create examination record
            $examination = CreateExamination::run($examinationData, $hbl->id);

            // Get only packages that were released from bonded area (release_status = 'released')
            $releasedFromBondPackages = $hbl->packages()->where('release_status', 'released')->get();

            if ($releasedFromBondPackages->isEmpty()) {
                throw new \Exception('No packages have been released from the Bonded Area. Please release packages from the Package Queue first.');
            }

            // Validate that selected packages are actually released from bond
            $selectedPackageIds = array_keys(array_filter($data['released_packages'] ?? []));
            
            if (empty($selectedPackageIds)) {
                throw new \Exception('Please select at least one package to release.');
            }

            // Process only the selected packages
            foreach ($selectedPackageIds as $packageId) {
                // Create package examination record for selected packages
                PackageExamination::create([
                    'hbl_package_id' => $packageId,
                    'examination_id' => $examination->id,
                    'customer_queue_id' => $customerQueue->id,
                    'token_id' => $customerQueue->token_id,
                    'action' => 'released',
                    'note' => $data['note'] ?? null,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
            }

            // Now check if there are any packages from bonded area that haven't been examined yet
            // Get all packages released from bonded area
            $allReleasedFromBond = $hbl->packages()
                ->where('release_status', 'released')
                ->with(['packageExaminations' => function ($query) {
                    $query->latest();
                }])
                ->get();

            // Count packages that still need examination
            $packagesNeedingExamination = $allReleasedFromBond->filter(function ($package) {
                $latestExamination = $package->packageExaminations->first();
                // Package needs examination if it has no examination record or latest action is not 'released'
                return !$latestExamination || $latestExamination->action !== 'released';
            })->count();

            $allPackagesExamined = $packagesNeedingExamination === 0;

            // Update package queue status
            $packageQueue = PackageQueue::where('token_id', $customerQueue->token_id)->first();
            if ($packageQueue) {
                $packageQueue->update([
                    'status' => $allPackagesExamined ? 'completed' : 'partial',
                    'completed_at' => $allPackagesExamined ? now() : null,
                ]);
            }

            // Update customer queue - only set left_at if ALL packages are examined and released
            if ($customerQueue) {
                // Only mark as left if all packages from bonded area have been released from examination
                if ($allPackagesExamined) {
                    $customerQueue->update([
                        'left_at' => now(),
                    ]);

                    // Set queue status log
                    $customerQueue->addQueueStatus(
                        CustomerQueue::EXAMINATION_QUEUE,
                        $customerQueue->token->customer_id,
                        $customerQueue->token_id,
                        null,
                        now(),
                    );
                }
                // If some packages still need examination, don't update left_at
                // The token should remain in the examination queue
            }

            // Mark HBL as released only if ALL packages from bonded area are released from examination
            if ($allPackagesExamined) {
                $hbl->is_released = true;
                $hbl->save();
            }

            // Send feedback mail
            $emailData = [
                'customerId' => $customerQueue->token->customer_id,
                'hblId' => $hbl->id,
                'tokenId' => $customerQueue->token_id,
            ];
            SendFeedbackMail::run($emailData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create examination record when releasing hbl packages: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->where('type', CustomerQueue::EXAMINATION_QUEUE)
            ->whereNotNull('left_at')
            ->has('token.verification')
            ->has('examination')
            ->with(['token.customer', 'token.reception', 'token.hbl', 'examination.releasedBy']);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ExaminationCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }

    public function markAsDeparted(CustomerQueue $customerQueue)
    {
        try {
            DB::beginTransaction();

            $customerQueue->token->update([
                'departed_at' => now(),
                'departed_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to mark as depart: '.$e->getMessage());
        }
    }

    public function returnPackagesToBond(array $data): void
    {
        try {
            DB::beginTransaction();

            $token = \App\Models\Token::find($data['token_id']);
            $hbl = HBL::withoutGlobalScopes()->where('reference', $token->reference)->first();

            foreach ($data['package_ids'] as $packageId) {
                $package = HBLPackage::find($packageId);
                
                if (!$package || $package->release_status !== 'held') {
                    continue;
                }

                // Update package status to returned_to_bond
                $package->update([
                    'release_status' => 'returned_to_bond',
                    'release_note' => $data['note'] ?? 'Returned to bond storage',
                ]);

                // Create package examination record
                PackageExamination::create([
                    'hbl_package_id' => $packageId,
                    'examination_id' => $data['examination_id'] ?? null,
                    'customer_queue_id' => $data['customer_queue_id'] ?? null,
                    'token_id' => $token->id,
                    'action' => 'returned_to_bond',
                    'note' => $data['note'] ?? 'Returned to bond storage',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
            }

            // Update package queue
            $packageQueue = PackageQueue::where('token_id', $token->id)->first();
            if ($packageQueue) {
                $returnedCount = count($data['package_ids']);
                $packageQueue->update([
                    'held_package_count' => $packageQueue->held_package_count - $returnedCount,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to return packages to bond: '.$e->getMessage());
        }
    }

    public function completeToken(array $data): void
    {
        try {
            DB::beginTransaction();

            $token = \App\Models\Token::find($data['token_id']);
            $hbl = HBL::withoutGlobalScopes()->where('reference', $token->reference)->first();

            // Check if all packages are either released or returned to bond
            $pendingPackages = $hbl->packages()
                ->whereIn('release_status', ['pending', 'held'])
                ->count();

            if ($pendingPackages > 0) {
                throw new \Exception('Cannot complete token. There are still pending or held packages.');
            }

            // Update package queue
            $packageQueue = PackageQueue::where('token_id', $token->id)->first();
            if ($packageQueue) {
                $packageQueue->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            // Update token status
            $token->update([
                'status' => 'completed',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to complete token: '.$e->getMessage());
        }
    }
}
