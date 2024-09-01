<?php

namespace App\Repositories\CallCenter;

use App\Actions\Examination\CreateExamination;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Mail\GetFeedback;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

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

            //send mail after 30min
            $feedbackURL = 'http://127.0.0.1:8000/your-feedback?user='.$data['customer_queue']['token']['customer_id'].'&hbl='.$hbl->id.'&token='.$data['customer_queue']['token_id'];
            $customer = User::find($data['customer_queue']['token']['customer_id']);
            // Schedule::call(function ($customer, $feedbackURL) {
            //     Mail::to('imalshaweerakkodi@gmail.com')->send(new GetFeedback($customer->name, $feedbackURL));
            // })->delay(now()->addMinutes(10));
            Mail::to($customer->email)
                ->later(now()->addMinutes(1), new GetFeedback($customer->name, $feedbackURL));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create examination record when releasing hbl packages: '.$e->getMessage());
        }
    }
}
