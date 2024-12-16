<?php

namespace App\Interfaces;

interface ShipperConsigneeRepositoryInterface
{
    public function getAllShipperConsignees();

    public function create(array $data);
}
