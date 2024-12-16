<?php

namespace App\Actions\HBL\CashSettlement;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLDOCharge
{
    use AsAction;

    public function handle(HBL $hbl, float $do_charge)
    {
        $hbl->update([
            'do_charge' => $do_charge,
        ]);
    }
}
