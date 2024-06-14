<?php

namespace App\Repositories;

use App\Actions\WarehouseZone\CreateWarehousezone;
use App\Actions\WarehouseZone\DeleteWarehousezone;
use App\Actions\Zone\GetZones;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Models\Branch;
use App\Models\WarehouseZone;

class WareahouseZoneRepository implements WarehousezoneRepositoryInterface
{
    public function getWarehouseZones()
    {
        // return GetZones::run();
    }

    public function createWarehouseZone(array $data)
    {
        return CreateWarehousezone::run($data);
    }

    public function editWarehouseZone(array $data, Branch $branch)
    {

    }

    public function destroy(WarehouseZone $wz): void
    {
        DeleteWarehousezone::run($wz);
    }
}
//
