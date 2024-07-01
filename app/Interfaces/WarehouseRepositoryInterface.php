<?php

namespace App\Interfaces;

use App\Models\HBL;

interface WarehouseRepositoryInterface
{
    public function getSummery(array $filters = []);

    public function assignWarehouseZone(HBL $hbl, int $warehouse_zone_id);
}
