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
    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();

            $hbl = HBL::withoutGlobalScopes()->where('reference', $data['customer_queue']['token']['reference'])->first();

            // Create examination record
            $examination = CreateExamination::run($data, $hbl->id);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

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

            // Process only the selected packages that were released from bonded area
            $releasedCount = 0;
            $heldCount = 0;

            foreach ($releasedFromBondPackages as $package) {
                // Check if this package was selected in the form
                $isSelected = in_array($package->id, $selectedPackageIds);
                
                // Determine action: 'released' (from examination) or 'held' (in examination)
                $packageAction = $isSelected ? 'released' : 'held';
                
                // Update package status
                // Note: We don't change release_status here as it's already 'released' from bond
                // We track examination release separately
                
                // Create package examination record
                PackageExamination::create([
                    'hbl_package_id' => $package->id,
                    'examination_id' => $examination->id,
                    'customer_queue_id' => $customerQueue->id,
                    'token_id' => $customerQueue->token_id,
                    'action' => $packageAction,
                    'note' => $data['note'] ?? null,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                if ($packageAction === 'released') {
                    $releasedCount++;
                } else {
                    $heldCount++;
                }
            }

            // Update package queue status
            $packageQueue = PackageQueue::where('token_id', $customerQueue->token_id)->first();
            if ($packageQueue) {
                $packageQueue->update([
                    'status' => $heldCount === 0 ? 'completed' : 'partial',
                    'completed_at' => $heldCount === 0 ? now() : null,
                ]);
            }

            // Update customer queue
            if ($customerQueue) {
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

            // Mark HBL as released only if ALL packages from bonded area are released from examination
            if ($heldCount === 0 && $releasedCount > 0) {
                $hbl->is_released = true;
                $hbl->save();
            }

            // Send feedback mail
            $emailData = [
                'customerId' => $data['customer_queue']['token']['customer_id'],
                'hblId' => $hbl->id,
                'tokenId' => $data['customer_queue']['token_id'],
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
