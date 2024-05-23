<?php

namespace App\Interfaces;

use App\Models\PickUp;
use Illuminate\Http\Request;

interface PickupRepositoryInterface
{
    public function getPickups();

    public function storePickup(array $data);

    public function getNoteTypes();

    public function assignDriver(array $data, PickUp $pickUp);

    public function getFilteredPickups(Request $request);

    public function savePickupOrder(array $pickups);
}
