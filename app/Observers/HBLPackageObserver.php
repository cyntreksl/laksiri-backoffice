<?php

namespace App\Observers;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBL\GetHBLById;
use App\Actions\HBLPackageRuleData\UpdateHBLPackageRuleData;
use App\Models\HBLPackage;
use App\Models\HBLPackageRuleData;
use App\Models\PackagePrice;

class HBLPackageObserver
{
    /**
     * Handle the HBLPackage "created" event.
     */
    public function created(HBLPackage $hBLPackage): void
    {
        $hbl = GetHBLById::run($hBLPackage['hbl_id']);
        $rules = [];
        if (! $hBLPackage['package_rule'] && $hBLPackage['package_rule'] <= 0) {
            $rules = GetPriceRulesByCargoModeAndHBLType::run($hbl['cargo_type'], $hbl['hbl_type'], $hbl['warehouse_id']);
        } else {
            $rules = PackagePrice::where('id', $hBLPackage['package_rule'])->get();
        }

        $package_rule_data = new HBLPackageRuleData;
        $package_rule_data->package_id = $hBLPackage['id'];
        $package_rule_data->is_package_rule = (bool) $hbl['package_rule'];
        $package_rule_data->rules = json_encode($rules);
        $package_rule_data->save();
    }

    /**
     * Handle the HBLPackage "updated" event.
     */
    public function updated(HBLPackage $hBLPackage): void
    {
        if ($hBLPackage->wasChanged('package_rule') && $hBLPackage['package_rule'] > 0) {
            $rules = PackagePrice::where('id', $hBLPackage['package_rule'])->get();
            $data['rules'] = json_encode($rules);
            $package_rule_data = UpdateHBLPackageRuleData::run($hBLPackage, $data);
        }
    }

    /**
     * Handle the HBLPackage "deleted" event.
     */
    public function deleted(HBLPackage $hBLPackage): void
    {
        //
    }

    /**
     * Handle the HBLPackage "restored" event.
     */
    public function restored(HBLPackage $hBLPackage): void
    {
        //
    }

    /**
     * Handle the HBLPackage "force deleted" event.
     */
    public function forceDeleted(HBLPackage $hBLPackage): void
    {
        //
    }
}
