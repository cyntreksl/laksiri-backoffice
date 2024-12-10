<?php

namespace App\Actions\HBL\CashSettlement;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPayments
{
    use AsAction;

    public function handle(float $total_paid_amount, HBL $hbl)
    {
        $hbl->update([
            'paid_amount' => $total_paid_amount,
        ]);

        $hbl->hblPayment()->withoutGlobalScopes()->updateOrCreate([
            'hbl_id' => $hbl->id,
        ], [
            'branch_id' => GetUserCurrentBranchID::run(),
            'grand_total' => $hbl->grand_total,
            'paid_amount' => $total_paid_amount,
            'status' => GetHBLPaymentStatus::run($total_paid_amount, $hbl->grand_total),
            'created_by' => auth()->id(),
        ]);
    }
}
