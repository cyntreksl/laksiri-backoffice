<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLNumber
{
    use AsAction;

    public function handle($currentBranchId)
    {
        $last_hbl = HBL::where('branch_id', $currentBranchId)->latest()->first();
        $next_number = $last_hbl ? ((int) substr($last_hbl->hbl_number, 3) + 1) : 1;
        $hbl_number = strtoupper(str_pad($currentBranchId, 3, '0', STR_PAD_LEFT)).str_pad($next_number, 6, '0', STR_PAD_LEFT);
        do {
            $exists = HBL::withoutGlobalScopes()->where('hbl_number', $hbl_number)->exists();
            $hbl_number++;
        } while ($exists);

        return $hbl_number;

    }
}
