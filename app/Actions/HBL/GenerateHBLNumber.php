<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLNumber
{
    use AsAction;

    public function handle($currentBranch)
    {
        $last_hbl = HBL::latest()->first();
        $next_number = $last_hbl ? ((int) substr($last_hbl->hbl_number, 6) + 1) : 000001;
        $hbl_number = strtoupper(substr($currentBranch, 0, 2)).str_pad($next_number, 6, '0', STR_PAD_LEFT);

        return $hbl_number;
    }
}
