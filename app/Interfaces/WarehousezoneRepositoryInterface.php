<?php

namespace App\Interfaces;

use App\Models\WarehouseZone;

interface WarehousezoneRepositoryInterface
{
    public function getWarehouseZone($id);

    public function createWarehouseZone(array $data);

    public function editWarehouseZone(array $data);

    public function destroy(WarehouseZone $warehouseZone);
}
