<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsLoaded
{
    use AsAction;

    public function handle($hbl_package_id)
    {
        $hbl_package = HBLPackage::find($hbl_package_id);
        $hbl_package->is_loaded = true;
        $hbl_package->save();
    }
}
