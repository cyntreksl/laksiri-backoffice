<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsFullyUnloaded
{
    use AsAction;

    public function handle($hbl_package_id)
    {
        $hbl_package = HBLPackage::withoutGlobalScope(BranchScope::class)
            ->find($hbl_package_id);
        $hbl_package->is_unloaded = true;
        $hbl_package->save();
    }
}
