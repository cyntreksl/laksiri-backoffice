<?php

namespace App\Actions\Cashier;

use App\Models\CashierHBLPayment;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCashierHBLPayments
{
    use AsAction;

    public function handle(array $data, HBL $hbl, float|string $paid_amount)
    {
        // Determine if this is a verification (already paid, cashier is just verifying)
        $isVerification = $paid_amount == 0;
        
        // Set verified_at when:
        // 1. It's a verification (paid_amount == 0) - already paid elsewhere, cashier is confirming
        // 2. OR when a payment is made (paid_amount > 0) - cashier is processing payment and confirming
        $shouldVerify = $isVerification || $paid_amount > 0;
        
        CashierHBLPayment::create([
            'verified_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'hbl_id' => $hbl->id,
            'paid_amount' => $paid_amount,
            'note' => $data['note'],
            'verified_at' => $shouldVerify ? now() : null,
        ]);
    }
}
