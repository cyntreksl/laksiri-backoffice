<?php

namespace App\Observers;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBL\GetHBLById;
use App\Actions\HBLPackageRuleData\UpdateHBLPackageRuleData;
use App\Models\HBLPackage;
use App\Models\HBLPackageRuleData;
use App\Models\PackagePrice;
use App\Services\ShortlandHandlingService;

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
            // Use warehouse_id if available, otherwise fall back to current branch ID
            $destinationBranchId = $hbl['warehouse_id'] ?? \App\Actions\User\GetUserCurrentBranchID::run();
            $rules = GetPriceRulesByCargoModeAndHBLType::run($hbl['cargo_type'], $hbl['hbl_type'], $destinationBranchId);
        } else {
            $rules = PackagePrice::where('id', $hBLPackage['package_rule'])->get();
        }

        $package_rule_data = new HBLPackageRuleData;
        $package_rule_data->package_id = $hBLPackage['id'];
        $package_rule_data->is_package_rule = $hBLPackage['package_rule'] ? true : false;
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

        // Auto-fix Shortland when package is unloaded
        if ($hBLPackage->wasChanged('is_unloaded') && $hBLPackage->is_unloaded) {
            $shortlandService = app(ShortlandHandlingService::class);
            $shortlandService->checkAndAutoFixShortland($hBLPackage);
        }
    }

    public function deleted(HBLPackage $hBLPackage): void
    {
        $hbl = $hBLPackage->hbl;

        if (! $hbl) {
            return; // No related HBL, nothing to do
        }

        $existPackages = $hbl->packages;

        // Only delete HBL if there are no packages AND it's not an API request
        if ($existPackages->isEmpty() && ! request()->is('v1/*')) {
            $hbl->delete();
        }
    }
}
