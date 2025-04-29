<?php

namespace App\Actions\HBL\CashSettlement;

use App\Enum\HBLPaymentStatus;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLPaymentStatus
{
    use AsAction;

    public function handle(float $total_paid_amount, float $grand_total): string
    {
        $total_paid_amount = round($total_paid_amount, 2);
        $grand_total = round($grand_total, 2);

        if ($total_paid_amount == 0.0) {
            return HBLPaymentStatus::NOT_PAID->value;
        }

        if ($total_paid_amount >= $grand_total) {
            return HBLPaymentStatus::FULL_PAID->value;
        }

        return HBLPaymentStatus::PARTIAL_PAID->value;
    }
}
