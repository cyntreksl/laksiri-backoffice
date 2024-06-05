<?php

namespace App\Http\Controllers;

use App\Actions\Zone\GetZones;
use App\Enum\CargoType;
use App\Http\Requests\AssignDriverRequest;
use App\Http\Requests\StorePickupRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupController extends Controller
{
    public function __construct(
        private readonly PickupRepositoryInterface $pickupRepository,
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Pickup/PendingJobs', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'users' => $this->userRepository->getUsers(),
            'zones' => $this->zoneRepository->getZones(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isUrgent', 'isImportant', 'createdBy', 'zoneBy']);

        return $this->pickupRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function create()
    {
        return Inertia::render('Pickup/CreateJob', [
            'cargoTypes' => CargoType::cases(),
            'noteTypes' => $this->pickupRepository->getNoteTypes(),
            'zones' => GetZones::run(),
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

    public function assignDriver(AssignDriverRequest $request)
    {
        return $this->pickupRepository->assignDriverToPickups($request->all());
    }

    public function showPickupOrder(Request $request)
    {
        return Inertia::render('Pickup/PickupOrder', [
            'filters' => $request->only('fromDate', 'toDate', 'driverId'),
            'drivers' => $this->driverRepository->getAllDrivers(),
            'pickups' => $this->pickupRepository->getFilteredPickups($request),
        ]);
    }

    public function updatePickupOrder(Request $request)
    {
        if ($request->pickups) {
            $this->pickupRepository->savePickupOrder($request->pickups);
        }
    }
}
