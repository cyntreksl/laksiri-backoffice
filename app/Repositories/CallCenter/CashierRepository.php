<?php

namespace App\Repositories\CallCenter;

use App\Actions\Cashier\UpdateCashierHBLPayments;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Interfaces\CallCenter\CashierRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;

class CashierRepository implements CashierRepositoryInterface
{
    public function updatePayment(array $data): void
    {
        try {
            DB::beginTransaction();

            $hbl = HBL::where('reference', $data['customer_queue']['token']['reference'])->firstOrFail();

            $new_paid_amount = $data['paid_amount'];
            $old_paid_amount = $hbl->paid_amount;
            $total_paid_amount = $old_paid_amount + $new_paid_amount;

            UpdateHBLPayments::run($total_paid_amount, $hbl);

            UpdateCashierHBLPayments::run($data, $hbl, $new_paid_amount);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

            if ($customerQueue) {
                $customerQueue->update([
                    'left_at' => now(),
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::CASHIER_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    null,
                    now(),
                );

                // check payment status
                $payment = GetPaymentByReference::run($customerQueue->token->reference);

                // If $data is an object, convert it to an array
                $paymentArray = (array) $payment->getData();

                if (! empty($paymentArray)) {
                    if ($payment->getData()->paid_amount >= $payment->getData()->grand_total) {
                        // send examination queue
                        $customerQueue->create([
                            'type' => CustomerQueue::EXAMINATION_QUEUE,
                            'token_id' => $customerQueue->token_id,
                        ]);

                        // set queue status log
                        $customerQueue->addQueueStatus(
                            CustomerQueue::EXAMINATION_QUEUE,
                            $customerQueue->token->customer_id,
                            $customerQueue->token_id,
                            now(),
                            null,
                        );
                    } else {
                        // send to cashier queue
                        $customerQueue->create([
                            'type' => CustomerQueue::CASHIER_QUEUE,
                            'token_id' => $customerQueue->token_id,
                        ]);

                        // set queue status log
                        $customerQueue->addQueueStatus(
                            CustomerQueue::CASHIER_QUEUE,
                            $customerQueue->token->customer_id,
                            $customerQueue->token_id,
                            now(),
                            null,
                        );
                    }
                } else {
                    // send to cashier queue
                    $customerQueue->create([
                        'type' => CustomerQueue::CASHIER_QUEUE,
                        'token_id' => $customerQueue->token_id,
                    ]);

                    // set queue status log
                    $customerQueue->addQueueStatus(
                        CustomerQueue::CASHIER_QUEUE,
                        $customerQueue->token->customer_id,
                        $customerQueue->token_id,
                        now(),
                        null,
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update payments: '.$e->getMessage());
        }
    }
}
