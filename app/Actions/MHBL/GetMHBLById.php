<?php

namespace App\Actions\MHBL;

use App\Models\MHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetMHBLById
{
    use AsAction;

    public function handle($mhbl_Id)
    {
        return MHBL::where('id', $mhbl_Id)
            ->with(['shipper', 'warehouse', 'consignee', 'hbls.packages'])
            ->first();
    }
}
