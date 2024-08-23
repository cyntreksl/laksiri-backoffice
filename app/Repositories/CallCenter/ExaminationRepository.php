<?php

namespace App\Repositories\CallCenter;

use App\Actions\Examination\CreateExamination;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;

class ExaminationRepository implements ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();

            $hbl = HBL::where('reference', $data['customer_queue']['token']['reference'])->first();

            // release packages
            $examination = CreateExamination::run($data, $hbl->id);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

            if ($customerQueue) {
                $customerQueue->update([
                    'left_at' => now(),
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::EXAMINATION_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    null,
                    now(),
                );
            }

            // mark as released HBL

            $countReleasedPackages = count(array_filter($examination->released_packages, fn ($value) => $value === true));
            $countHBLPackages = $hbl->packages->count();

            if ($countHBLPackages === $countReleasedPackages) {
                $hbl->is_released = true;
                $hbl->save();
            }
            // generate gate pass with QR

            // update examinations table as an is_issued_gate_pass is true
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create examination record when releasing hbl packages: '.$e->getMessage());
        }
    }
}
