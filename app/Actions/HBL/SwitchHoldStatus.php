<?php

namespace App\Actions\HBL;

use App\Actions\HBL\HBLPackage\UpdateHBLPackage;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchHoldStatus
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $hbl->is_hold = ! $hbl->is_hold;
        $hbl->save();

        if ($hbl->packages->count() > 0) {
            foreach ($hbl->packages as $package) {
                UpdateHBLPackage::run($package, ['is_hold' => ! $package->is_hold]);
            }
        }
    }
}
