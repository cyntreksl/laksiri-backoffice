<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\Api\PickupRepositoryInterface;
use App\Models\PickUp;

class PickupController extends Controller
{
    public function __construct(
        private readonly PickupRepositoryInterface $pickupRepository,
    ) {
    }

    public function index()
    {
        return $this->pickupRepository->getPendingPickupsForDriver();
    }

    public function show(PickUp $pickup)
    {
        return $this->pickupRepository->showPickup($pickup);
    }
}
