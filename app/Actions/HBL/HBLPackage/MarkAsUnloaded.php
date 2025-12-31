<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsUnloaded
{
    use AsAction;

    public function handle($hbl_package_id)
    {
        if (Auth::user()->hasRole('boned area')) {
            $hbl_package = HBLPackage::find($hbl_package_id);
            $hbl_package->is_de_loaded = false;
            $hbl_package->unloaded_at = now();
            $hbl_package->unloaded_by = auth()->id();
            $hbl_package->save();
        } else {
            $hbl_package = HBLPackage::find($hbl_package_id);
            $hbl_package->is_loaded = false;
            $hbl_package->unloaded_at = now();
            $hbl_package->unloaded_by = auth()->id();
            $hbl_package->save();
        }
    }
}
