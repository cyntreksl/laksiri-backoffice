<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLCharges
{
    use AsAction;

    public function handle(HBL $HBL) {
        $branchId = $HBL->branch_id;
        $currencyCode = $HBL->branch->currency_symbol;
        $currencyRateInLKR = $HBL->currency_rate;
        $isBranchPrepaid = $HBL->branch->is_prepaid;
        $applicableTaxes = Tax::whereIsActive(true)->get();

        $freightCharge = $HBL->freight_charge;
        $billCharge = $HBL->bill_charge;
        $packageCharge = 0; //TODO: Implement package charge logic

        $departure1Total = $freightCharge + $billCharge + $packageCharge;

        $destinationHandlingCharge = 0; //TODO: Implement destination handling charge logic

    }
}
