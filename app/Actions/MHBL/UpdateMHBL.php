<?php

namespace App\Actions\MHBL;

use App\Models\Mhbl;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMHBL
{
    use AsAction;

    public function handle(MHBL $mhbl, array $data): MHBL
    {
        $mhbl->update([
            'consignee_id' => $data['consignee_id'],
            'shipper_id' => $data['shipper_id'],
            'grand_volume' => $data['grand_volume'],
            'grand_weight' => $data['grand_weight'],
            'grand_total' => $data['grand_total'],
        ]);

        return $mhbl;
    }
}
