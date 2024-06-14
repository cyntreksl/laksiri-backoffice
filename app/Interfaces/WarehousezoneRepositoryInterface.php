<?php

namespace App\Interfaces;

use App\Models\Branch;
use App\Models\WarehouseZone;

interface WarehousezoneRepositoryInterface
{
    public function getWarehouseZones();

    public function createWarehouseZone(array $data);

    public function editWarehouseZone(array $data, Branch $branch);

    public function destroy(WarehouseZone $warehouseZone);
}
