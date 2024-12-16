<?php

namespace App\Repositories;


use App\Interfaces\ShipperConsigneeRepositoryInterface;
use App\Models\ShippersConsignees;


class ShipperConsigneeRepository implements ShipperConsigneeRepositoryInterface
{
    public function getAllShipperConsignees()
    {

    }

    public function create(array $data)
    {
        return ShippersConsignees::create($data);
    }


}
