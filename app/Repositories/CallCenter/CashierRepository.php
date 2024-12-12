<?php

namespace App\Repositories\CallCenter;

use App\Actions\Cashier\DownloadGatePassPDF;
use App\Actions\Cashier\UpdateCashierHBLPayments;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Http\Resources\CallCenter\PaidCollection;
use App\Interfaces\CallCenter\CashierRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\PackageQueue;
use Illuminate\Support\Facades\DB;

class CashierRepository implements CashierRepositoryInterface, GridJsInterface
{
    public function updatePayment(array $data): void
    {
        try {
            DB::beginTransaction();

            $hbl = HBL::where('reference', $data['customer_queue']['token']['reference'])->withoutGlobalScopes()->firstOrFail();

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
                    if ($data['paid_amount'] >= $payment->getData()->grand_total - $payment->getData()->paid_amount) {
                        // send examination queue
                        $customerQueue->create([
                            'type' => CustomerQueue::EXAMINATION_QUEUE,
                            'token_id' => $customerQueue->token_id,
                        ]);

                        // create package queue
                        PackageQueue::create([
                            'token_id' => $customerQueue->token_id,
                            'hbl_id' => $hbl->id,
                            'auth_id' => auth()->id(),
                            'reference' => $data['customer_queue']['token']['reference'],
                            'package_count' => $data['customer_queue']['token']['package_count'],
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

            //            DownloadGatePassPDF::run($hbl->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update payments: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->cashierQueue()
            ->has('token.cashierPayment');

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $query->count();

        return response()->json([
            'data' => PaidCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function downloadGatePass($hbl)
    {
        return DownloadGatePassPDF::run($hbl);
    }
}
