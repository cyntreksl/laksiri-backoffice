<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Http\Requests\StorePickupRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupController extends Controller
{
    public function __construct(
        private readonly PickupRepositoryInterface $pickupRepository,
        private readonly DriverRepositoryInterface $driverRepository
    )
    {
    }

    public function index()
    {
        $pickups = $this->pickupRepository->getPickups();

        return Inertia::render('Pickup/PendingJobs', [
            'pickups' => $pickups,
            'drivers' => $this->driverRepository->getAllDrivers(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Pickup/CreateJob', [
            'cargoTypes' => CargoType::cases(),
            'noteTypes' => $this->pickupRepository->getNoteTypes(),
        ]);
    }

    public function store(StorePickupRequest $request)
    {
        $this->pickupRepository->storePickup($request->all());
    }

    public function show(PickUp $pickUp)
    {
    }

    public function edit(PickUp $pickUp)
    {
    }

    public function update(Request $request, PickUp $pickUp)
    {
    }

    public function destroy(PickUp $pickUp)
    {
    }

    public function updateDriver(Request $request, $pickUp)
    {
        $pickUp = PickUp::find($pickUp);
        $this->pickupRepository->assignDriver($request->all(), $pickUp);
    }
}
