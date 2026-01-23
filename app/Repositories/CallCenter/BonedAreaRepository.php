<?php

namespace App\Repositories\CallCenter;

use App\Actions\PackageQueue\UpdatePackageQueue;
use App\Http\Resources\CallCenter\PackageCollection;
use App\Interfaces\CallCenter\BonedAreaRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\PackageQueue;
use Illuminate\Support\Facades\DB;

use App\Models\CustomerQueue;
use App\Models\Token;

class BonedAreaRepository implements BonedAreaRepositoryInterface, GridJsInterface
{
    public function releasePackage(array $data): void
    {
        try {
            DB::beginTransaction();

            $packageQueue = PackageQueue::with('token')->find($data['package_queue']['id']);
            
            if (!$packageQueue) {
                throw new \Exception('Package queue not found');
            }

            // Get the selected package IDs from released_packages
            $selectedPackageIds = array_keys(array_filter($data['released_packages'] ?? []));
            
            if (empty($selectedPackageIds)) {
                throw new \Exception('No packages selected for release');
            }

            // Check if any selected packages are already released
            $alreadyReleasedPackages = \App\Models\HBLPackage::whereIn('id', $selectedPackageIds)
                ->where('release_status', 'released')
                ->get();

            if ($alreadyReleasedPackages->isNotEmpty()) {
                $packageList = $alreadyReleasedPackages->map(function($pkg) {
                    return "{$pkg->package_type} (Qty: {$pkg->quantity})";
                })->join(', ');
                
                throw new \Exception("The following package(s) have already been released and cannot be released again: {$packageList}. Please refresh the page to see the current status.");
            }

            // Get packages that can be released (pending, held, or returned_to_bond)
            $packagesToRelease = \App\Models\HBLPackage::whereIn('id', $selectedPackageIds)
                ->whereIn('release_status', ['pending', 'held', 'returned_to_bond'])
                ->get();

            if ($packagesToRelease->isEmpty()) {
                throw new \Exception('All selected packages have already been released. Please refresh the page.');
            }

            // Update only the packages that are not already released
            \App\Models\HBLPackage::whereIn('id', $packagesToRelease->pluck('id'))
                ->update([
                    'release_status' => 'released',
                    'released_at' => now(),
                    'released_by' => auth()->id(),
                    'release_note' => $data['note'] ?? null,
                ]);

            // Update the package queue with released package tracking
            $existingReleasedPackages = $packageQueue->released_packages ?? [];
            $newReleasedPackages = array_merge($existingReleasedPackages, $data['released_packages'] ?? []);
            
            // Count total packages and released packages
            $totalPackages = $packageQueue->package_count;
            $releasedCount = count(array_filter($newReleasedPackages));
            $heldCount = $totalPackages - $releasedCount;
            
            // Determine if all packages are released
            $allPackagesReleased = $heldCount === 0;
            
            $packageQueue->update([
                'released_packages' => $newReleasedPackages,
                'released_package_count' => $releasedCount,
                'held_package_count' => $heldCount,
                'is_released' => $allPackagesReleased,
                'released_at' => $allPackagesReleased ? now() : $packageQueue->released_at,
                'auth_id' => auth()->id(),
                'note' => $data['note'] ?? $packageQueue->note,
                'status' => $allPackagesReleased ? 'completed' : 'partial',
                'completed_at' => $allPackagesReleased ? now() : null,
            ]);

            // Create Examination Queue (Step 5) - only if packages are being released
            if ($packageQueue->token) {
                // Check if examination queue already exists for this token
                $existingQueue = CustomerQueue::where('token_id', $packageQueue->token_id)
                    ->where('type', CustomerQueue::EXAMINATION_QUEUE)
                    ->whereNull('left_at')
                    ->first();
                
                if (!$existingQueue) {
                    // Create examination queue
                    $customerQueue = CustomerQueue::create([
                        'type' => CustomerQueue::EXAMINATION_QUEUE,
                        'token_id' => $packageQueue->token_id,
                    ]);

                    // Set queue status
                    $customerQueue->addQueueStatus(
                        CustomerQueue::EXAMINATION_QUEUE,
                        $packageQueue->token->customer_id,
                        $packageQueue->token_id,
                        now(),
                        null
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update package queue when releasing package: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PackageQueue::query()
            ->whereHas('token')
            ->where('is_released', true);

        // Handle ordering by token value
        if ($order === 'token') {
            $query->join('tokens', 'package_queues.token_id', '=', 'tokens.id')
                ->orderBy('tokens.token', $direction);
        } else {
            $query->orderBy($order, $direction);
        }

        $records = $query->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PackageCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }
}
