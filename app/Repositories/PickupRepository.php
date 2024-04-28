<?php

namespace App\Repositories;

use App\Actions\PickUps\CreatePickUp;
use App\Enum\CargoType;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;

class PickupRepository implements PickupRepositoryInterface
{

    public function storePickup(array $data)
    {
        // assign agent

        // assign location longitude, latitude and name

        // store pickup
        return CreatePickUp::run($data);
    }
}
