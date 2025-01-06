<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\MHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLNumber
{
    use AsAction;

    public function handle($currentBranchId)
    {
        $last_hbl = HBL::where('branch_id', $currentBranchId)->latest()->first();
        $last_mhbl = MHBL::where('branch_id', $currentBranchId)->latest()->first();
        if ($last_mhbl && (int) $last_mhbl->hbl_number > (int) $last_hbl->hbl_number) {
            $next_number = $last_mhbl->hbl_number ? ((int) substr($last_mhbl->hbl_number, 3) + 1) : 1;
        } else {
            $next_number = $last_hbl ? ((int) substr($last_hbl->hbl_number, 3) + 1) : 1;
        }
        do {
            $hbl_number = strtoupper(str_pad($currentBranchId, 3, '0', STR_PAD_LEFT)).str_pad($next_number, 6, '0', STR_PAD_LEFT);
            $exists = HBL::withoutGlobalScopes()->where('hbl_number', $hbl_number)->exists();
            $exists_mhbl = MHBL::withoutGlobalScopes()->where('hbl_number', $hbl_number)->exists();
            $next_number++;
        } while ($exists || $exists_mhbl);

        return $hbl_number;

    }
}
