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
        $hbl_package = HBLPackage::withoutGlobalScope(BranchScope::class)->with('hbl')
            ->find($hbl_package_id);
        $hbl_package->is_unloaded = true;
        $hbl_package->unloaded_at = now();
        $hbl_package->unloaded_by = auth()->id();
        $hbl_package->current_warehouse = session('current_branch_id');
        if ($hbl_package->hbl->warehouse_id === session('current_branch_id')) {
            $hbl_package->is_de_unloaded = true;
        }
        $hbl_package->save();
    }
}
