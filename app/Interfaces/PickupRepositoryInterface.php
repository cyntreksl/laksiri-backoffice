<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface PickupRepositoryInterface
{
    public function getPickups();

    public function storePickup(array $data);

    public function getNoteTypes();

    public function assignDriverToPickups(array $data);

    public function getFilteredPickups(Request $request);

    public function savePickupOrder(array $pickups);
}
