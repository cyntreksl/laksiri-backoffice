<?php

namespace App\Actions\WarehouseZone;

use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteWarehousezone
{
    use AsAction;

    public function handle(WarehouseZone $warehouseZone)
    {
        $warehouseZone->delete();
    }
}
