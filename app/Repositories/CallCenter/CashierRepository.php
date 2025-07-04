<?php

namespace App\Repositories\CallCenter;

use App\Actions\Cashier\DownloadCashierInvoicePDF;
use App\Actions\Cashier\UpdateCashierHBLPayments;
use App\Actions\HBL\CashSettlement\UpdateHBLDOCharge;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Actions\HBL\Payments\CreateHBLPayment;
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

            $newPaidAmount = (float) ($data['paid_amount'] ?? 0);
            $previousPaidAmount = (float) ($hbl->paid_amount ?? 0);
            $grandTotal = (float) ($hbl->grand_total ?? 0);

            $updatedPaidAmount = $previousPaidAmount + $newPaidAmount;
            $dueAmount = max(0, $grandTotal - $updatedPaidAmount);

            $hblUpdateData = array_merge($data, [
                'paid_amount' => $updatedPaidAmount,
            ]);

            UpdateHBLPayments::run($hblUpdateData, $hbl);

            // Payment creation
            CreateHBLPayment::run([
                'hbl_id' => $hbl->id,
                'base_currency_rate_in_lkr' => $hbl->currency_rate,
                'paid_amount' => $newPaidAmount,
                'total_amount' => $grandTotal - $previousPaidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'paid_by' => auth()->id(),
                'notes' => $data['payment_notes'] ?? 'Payment was updated from cashier',
            ]);

            UpdateHBLDOCharge::run($hbl, $data['do_charge']);

            UpdateCashierHBLPayments::run($data, $hbl, $newPaidAmount);

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
                        // send it to the cashier queue
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
                    // send it to the cashier queue
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

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->cashierQueue()
            ->has('token.cashierPayment');

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PaidCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }

    public function downloadCashierInvoice($hbl)
    {
        return DownloadCashierInvoicePDF::run($hbl);
    }
}
