<?php

namespace App\Actions\MHBL;

use App\Models\HBL;
use App\Models\MHBLsHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteMHBLsHBL
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        MHBLsHBL::where('hbl_id', $hbl->id)->delete();
    }
}
