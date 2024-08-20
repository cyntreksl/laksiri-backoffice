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
        CashierHBLPayment::create([
            'verified_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'hbl_id' => $hbl->id,
            'paid_amount' => $paid_amount,
            'note' => $data['note'],
        ]);
    }
}
