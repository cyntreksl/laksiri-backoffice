<?php

namespace App\Actions\HBL\Warehouse;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignZone
{
    use AsAction;

    public function handle(HBL $hbl, int $warehouse_zone_id)
    {
        $hbl->update([
            'warehouse_zone_id' => $warehouse_zone_id,
        ]);
    }
}
