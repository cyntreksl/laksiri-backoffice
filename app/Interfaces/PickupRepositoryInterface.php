<?php

namespace App\Interfaces;

use App\Models\PickUp;
use Illuminate\Http\Request;

interface PickupRepositoryInterface
{
    public function getPickups();

    public function storePickup(array $data);

    public function getNoteTypes();

    public function assignDriverToPickups(array $data);

    public function getFilteredPickups(Request $request);

    public function savePickupOrder(array $pickups);

    public function updatePickup(array $data, PickUp $pickup);

    public function deletePickup(PickUp $pickup);

    public function deletePickups(array $pickupIds);

    public function export(array $filters);
}
