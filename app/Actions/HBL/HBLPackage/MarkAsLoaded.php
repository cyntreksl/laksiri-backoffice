<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsLoaded
{
    use AsAction;

    public function handle($hbl_package_id, $is_destination_loaded = false)
    {
        if (Auth::user()->hasRole('boned area')) {
            $hbl_package = HBLPackage::find($hbl_package_id);
            $hbl_package->is_de_loaded = true;
            $hbl_package->save();
        } else {
            $hbl_package = HBLPackage::find($hbl_package_id);
            $hbl_package->is_loaded = true;
            $hbl_package->save();
        }
    }
}
