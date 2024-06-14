<?php

namespace App\Actions\WarehouseZone;

use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWarehousezone
{
    use AsAction;

    public function handle(array $data)
    {
        $warehousezone = WarehouseZone::create($data);

        return $warehousezone;
    }
}
