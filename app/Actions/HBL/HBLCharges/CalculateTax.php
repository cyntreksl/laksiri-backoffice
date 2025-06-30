<?php

namespace App\Actions\HBL\HBLCharges;

use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateTax
{
    use AsAction;

    public function handle($amount)
    {
        $taxes = Tax::whereIsActive(true)->get();

        $totalTax = 0.0;
        foreach ($taxes as $tax) {
            $taxAmount = ($amount * $tax->rate) / 100;
            $totalTax += $taxAmount;
        }

        return [
            'total_tax' => $totalTax,
            'amount_with_tax' => $amount + $totalTax,
            'taxes' => $taxes,
        ];
    }
}
