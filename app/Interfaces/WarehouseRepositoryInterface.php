<?php

namespace App\Interfaces;

interface WarehouseRepositoryInterface
{
    public function getSummery(array $filters = []);
}
