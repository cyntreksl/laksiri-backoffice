<?php

namespace App\Repositories\CallCenter;

use App\Actions\Examination\CreateExamination;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Models\CustomerQueue;
use Illuminate\Support\Facades\DB;

class ExaminationRepository implements ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();
            // release packages
            CreateExamination::run($data);

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

            // mark as released HBL packages one by one

            // mark as released HBL

            // generate gate pass with QR

            // update examinations table as an is_issued_gate_pass is true
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create examination record when releasing hbl packages: '.$e->getMessage());
        }
    }
}
