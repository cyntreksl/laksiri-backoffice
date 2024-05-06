<?php

namespace App\Interfaces;

use App\Models\PickUp;

interface PickupRepositoryInterface
{
    public function getPickups();

    public function storePickup(array $data);

    public function getNoteTypes();

    public function assignDriver(array $data, PickUp $pickUp);
}
