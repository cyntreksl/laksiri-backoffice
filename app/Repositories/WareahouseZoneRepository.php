<?php

namespace App\Repositories;

use App\Actions\WarehouseZone\CreateWarehousezone;
use App\Actions\WarehouseZone\DeleteWarehousezone;
use App\Actions\WarehouseZone\GetWarehousezone;
use App\Actions\WarehouseZone\UpdateWarehousezone;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Models\WarehouseZone;

class WareahouseZoneRepository implements WarehousezoneRepositoryInterface
{
    public function getWarehouseZone($id)
    {
        return GetWarehousezone::run($id);
    }

    public function createWarehouseZone(array $data)
    {
        return CreateWarehousezone::run($data);
    }

    public function editWarehouseZone(array $data)
    {
        return UpdateWarehousezone::run($data);
    }

    public function destroy(WarehouseZone $warehouseZone): void
    {
        DeleteWarehousezone::run($warehouseZone);
    }
}
//
