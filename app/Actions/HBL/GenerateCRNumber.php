<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateCRNumber
{
    use AsAction;

    public function handle($currentBranchId)
    {
        $last_cr = HBL::latest()->first();
        $next_number = $last_cr ? ((int) $last_cr->cr_number + 1) : 1;
        $cr_number = str_pad($next_number, 6, '0', STR_PAD_LEFT);

        return $cr_number;
    }
}
