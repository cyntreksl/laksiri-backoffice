<?php

namespace App\Repositories\CallCenter;

use App\Actions\Cashier\DownloadCashierInvoicePDF;
use App\Actions\Cashier\UpdateCashierHBLPayments;
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
        DB::beginTransaction();

        try {
            // 1. Retrieve HBL record
            $hbl = $this->getHBL($data['customer_queue']['token']['reference']);

            // 2. Process payment updates
            $this->processPaymentUpdates($hbl, $data);

            // 3. Handle queue updates
            $this->updateCustomerQueue($hbl, $data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update payments: '.$e->getMessage());
        }
    }

    protected function getHBL(string $reference): HBL
    {
        return HBL::where('reference', $reference)
            ->withoutGlobalScopes()
            ->firstOrFail();
    }

    private function processPaymentUpdates(HBL $hbl, array $data): void
    {
        $currencyRate = (float) ($hbl->currency_rate ?: 1);

        // Process additional charges if any
        $this->processAdditionalCharges($hbl, $data, $currencyRate);

        // Process discount if any
        $this->processDiscount($hbl, $data, $currencyRate);

        // Calculate payment amounts
        $paymentData = $this->calculatePaymentAmounts($hbl, $data, $currencyRate);

        // Update HBL payments
        UpdateHBLPayments::run($paymentData, $hbl);

        // Create a payment record
        $this->createPaymentRecord($hbl, $paymentData, $currencyRate);

        // Update cashier payments
        UpdateCashierHBLPayments::run($data, $hbl, $paymentData['new_paid_amount']);
    }

    private function processAdditionalCharges(HBL $hbl, array $data, float $currencyRate): void
    {
        if (! empty($data['additional_charges'])) {
            $additionalChargesInCurrency = round((float) $data['additional_charges'] / $currencyRate, 2);

            if ($destinationCharges = $hbl->destinationCharge) {
                $destinationCharges->destination_other_charge += $additionalChargesInCurrency;
                $destinationCharges->save();
            }
        }
    }

    private function processDiscount(HBL $hbl, array $data, float $currencyRate): void
    {
        if (! empty($data['discount'])) {
            $discountInCurrency = round((float) $data['discount'] / $currencyRate, 2);

            // Apply discount to HBL or appropriate model
            $hbl->discount += $discountInCurrency;
            $hbl->save();
        }
    }

    private function createPaymentRecord(HBL $hbl, array $paymentData, float $currencyRate): void
    {
        $discountInCurrency = 0;

        if (! empty($paymentData['discount'])) {
            $discountInCurrency = round((float) $paymentData['discount'] / $currencyRate, 2);
        }

        CreateHBLPayment::run([
            'hbl_id' => $hbl->id,
            'base_currency_rate_in_lkr' => $hbl->currency_rate,
            'paid_amount' => $paymentData['new_paid_amount'],
            'total_amount' => ($hbl->grand_total - ($hbl->paid_amount ?? 0)) - $discountInCurrency,
            'due_amount' => $paymentData['due_amount'],
            'payment_method' => $paymentData['payment_method'] ?? 'cash',
            'paid_by' => auth()->id(),
            'notes' => $paymentData['payment_notes'] ?? 'Payment was updated from cashier',
        ]);
    }

    private function calculatePaymentAmounts(HBL $hbl, array $data, float $currencyRate): array
    {
        $newPaidAmountLKR = (float) ($data['paid_amount'] ?? 0);
        $newPaidAmount = round($newPaidAmountLKR / $currencyRate, 2);
        $previousPaidAmount = (float) ($hbl->paid_amount ?? 0);
        $grandTotal = (float) ($hbl->grand_total ?? 0);

        $updatedPaidAmount = $previousPaidAmount + $newPaidAmount;
        $dueAmount = max(0, $grandTotal - $updatedPaidAmount);

        return array_merge($data, [
            'paid_amount' => $updatedPaidAmount,
            'new_paid_amount' => $newPaidAmount,
            'due_amount' => $dueAmount,
        ]);
    }

    private function updateCustomerQueue(HBL $hbl, array $data): void
    {
        $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

        if (! $customerQueue) {
            return;
        }

        // Check if package queue already exists - if so, token has already moved to next step
        $existingPackageQueue = PackageQueue::where('token_id', $customerQueue->token_id)->first();
        if ($existingPackageQueue) {
            // Package queue exists, just mark current queue as completed and return
            $customerQueue->update(['left_at' => now()]);
            $customerQueue->addQueueStatus(
                CustomerQueue::CASHIER_QUEUE,
                $customerQueue->token->customer_id,
                $customerQueue->token_id,
                null,
                now()
            );
            return;
        }

        // Mark current queue as completed
        $customerQueue->update(['left_at' => now()]);
        $customerQueue->addQueueStatus(
            CustomerQueue::CASHIER_QUEUE,
            $customerQueue->token->customer_id,
            $customerQueue->token_id,
            null,
            now()
        );

        // Determine next queue based on payment status
        // Use HBL directly for payment information
        $currencyRate = (float) ($hbl->currency_rate ?: 1);
        $grandTotal = (float) ($hbl->grand_total ?? 0); // Already in base currency
        $currentPaidAmount = (float) ($hbl->paid_amount ?? 0); // Already in base currency
        $outstandingAmount = $grandTotal - $currentPaidAmount;
        
        // Form paid_amount is in LKR, convert to base currency for comparison
        $formPaidAmountLKR = (float) ($data['paid_amount'] ?? 0);
        $formPaidAmountBase = round($formPaidAmountLKR / $currencyRate, 2);

        // Check if this is a verification (paid_amount == 0 means already paid, just verifying)
        $isVerification = $formPaidAmountLKR == 0;

        // If it's a verification, check if HBL is already fully paid
        // If it's a payment, check if the payment amount covers the outstanding
        if ($isVerification) {
            // Verification: Check if HBL is already fully paid
            if ($outstandingAmount <= 0) {
                // Fully paid, move to package delivery
                $this->sendToPackageDelivery($customerQueue, $hbl, $data);
            } else {
                // Not fully paid, stay in cashier queue
                $this->sendToCashierQueue($customerQueue);
            }
        } else {
            // Payment: Check if payment covers outstanding amount
            // Calculate new paid amount after this payment (both in base currency)
            $newPaidAmount = $currentPaidAmount + $formPaidAmountBase;
            $newOutstanding = $grandTotal - $newPaidAmount;
            
            if ($newOutstanding <= 0) {
                // Payment covers all outstanding, move to package delivery
                $this->sendToPackageDelivery($customerQueue, $hbl, $data);
            } else {
                // Still has outstanding, stay in cashier queue
                $this->sendToCashierQueue($customerQueue);
            }
        }
    }

    private function sendToCashierQueue(CustomerQueue $customerQueue): void
    {
        // Check if there's already an active cashier queue for this token
        $existingQueue = CustomerQueue::where('token_id', $customerQueue->token_id)
            ->where('type', CustomerQueue::CASHIER_QUEUE)
            ->whereNull('left_at')
            ->first();

        if ($existingQueue) {
            // Queue already exists, don't create duplicate
            return;
        }

        CustomerQueue::create([
            'type' => CustomerQueue::CASHIER_QUEUE,
            'token_id' => $customerQueue->token_id,
        ]);

        $customerQueue->addQueueStatus(
            CustomerQueue::CASHIER_QUEUE,
            $customerQueue->token->customer_id,
            $customerQueue->token_id,
            now(),
            null
        );
    }

    private function sendToPackageDelivery(CustomerQueue $customerQueue, HBL $hbl, array $data): void
    {
        // Check if package queue already exists for this token
        $existingPackageQueue = PackageQueue::where('token_id', $customerQueue->token_id)->first();

        if ($existingPackageQueue) {
            // Package queue already exists, don't create duplicate
            return;
        }

        // Create package queue (Step 4: Waiting for Package Receive)
        // We DO NOT create Examination Queue here yet. That happens after package release.
        
        PackageQueue::create([
            'token_id' => $customerQueue->token_id,
            'hbl_id' => $hbl->id,
            'auth_id' => auth()->id(),
            'reference' => $data['customer_queue']['token']['reference'],
            'package_count' => $data['customer_queue']['token']['package_count'],
        ]);

        // Note: usage of addQueueStatus for ExaminationQueue removed here, 
        // as we are not in Examination Queue yet.
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->cashierQueue()
            ->has('token.cashierPayment')
            ->with([
                'token:id,token,package_count,reference,customer_id,receptionist_id',
                'token.customer:id,name',
                'token.reception:id,name',
                'token.cashierPayment:id,token_id,paid_amount,created_at,verified_by',
                'token.cashierPayment.verifiedBy:id,name',
            ]);

        // Apply customer filter
        if (! empty($filters['customer'])) {
            $query->whereHas('token.customer', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['customer'].'%');
            });
        }

        // Apply reception filter
        if (! empty($filters['reception'])) {
            $query->whereHas('token.reception', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['reception'].'%');
            });
        }

        // Apply verified_by filter
        if (! empty($filters['verified_by'])) {
            $query->whereHas('token.cashierPayment.verifiedBy', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['verified_by'].'%');
            });
        }

        // Apply paid_at filter
        if (! empty($filters['paid_at'])) {
            $query->whereHas('token.cashierPayment', function ($q) use ($filters) {
                $q->whereDate('created_at', $filters['paid_at']);
            });
        }

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
