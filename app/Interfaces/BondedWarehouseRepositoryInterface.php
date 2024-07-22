<?php

namespace App\Interfaces;

interface BondedWarehouseRepositoryInterface
{
    public function markAsShortLoading($hbl_id);

    public function export(array $filters);
}
