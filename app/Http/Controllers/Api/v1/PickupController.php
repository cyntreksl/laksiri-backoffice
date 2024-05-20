<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePickupRequest;
use App\Http\Requests\StorePickupToHBLRequest;
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

    public function store(StorePickupRequest $request)
    {
        return $this->pickupRepository->storePickup($request->all());
    }

    public function show(PickUp $pickup)
    {
        return $this->pickupRepository->showPickup($pickup);
    }

    public function pickupToHbl(PickUp $pickUp, StorePickupToHBLRequest $request)
    {
        return $this->pickupRepository->pickupToHbl($pickUp, $request);
    }
}
