<?php

namespace App\Actions\WarehouseZone;

use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWarehousezone
{
    use AsAction;

    public function handle(array $data)
    {

        $warehousezone = WarehouseZone::find($data['id']);

        $warehousezone->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return $warehousezone;

    }
}
