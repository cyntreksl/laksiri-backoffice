<?php

namespace App\Http\Controllers;

use App\Actions\Zone\GetZones;
use App\Enum\CargoType;
use App\Events\PickupCreated;
use App\Http\Requests\AssignDriverRequest;
use App\Http\Requests\StorePickupRequest;
use App\Http\Requests\UpdatePickupRequest;
use App\Http\Resources\PickupResource;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\NotificationMailRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Interfaces\SettingRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\PickUp;
use App\Models\PickupType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PickupRepositoryInterface $pickupRepository,
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
        private readonly CountryRepositoryInterface $countryRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly NotificationMailRepositoryInterface $notificationMailRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {}

    public function index()
    {
        $this->authorize('pickups.pending pickups');

        return Inertia::render('Pickup/PendingJobs', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'users' => $this->userRepository->getUsers(['customer']),
            'zones' => $this->zoneRepository->getZones(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->input('pickupDate')
            ? $request->only(['userData', 'cargoMode', 'isUrgent', 'isImportant', 'createdBy', 'driverBy', 'zoneBy', 'pickupDate', 'trashed'])
            : $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'isUrgent', 'isImportant', 'createdBy', 'driverBy', 'zoneBy', 'trashed']);

        return $this->pickupRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function create()
    {
        $this->authorize('pickups.create');

        return Inertia::render('Pickup/CreateJob', [
            'pickupTypes' => PickupType::pluck('pickup_type_name')->toArray(),
            'cargoTypes' => CargoType::cases(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'zones' => GetZones::run(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
        ]);
    }

    public function store(StorePickupRequest $request)
    {
        $pickup = $this->pickupRepository->storePickup($request->all());

        PickupCreated::dispatch($pickup);
    }

    public function show(PickUp $pickup)
    {
        $this->authorize('pickups.show');

        $pickupResource = new PickupResource($pickup);

        return response()->json($pickupResource);
    }

    public function edit(PickUp $pickup)
    {
        $this->authorize('pickups.edit');

        return Inertia::render('Pickup/EditJob', [
            'pickupTypes' => PickupType::pluck('pickup_type_name')->toArray(),
            'cargoTypes' => CargoType::cases(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'zones' => GetZones::run(),
            'pickup' => $pickup,
        ]);
    }

    public function update(UpdatePickupRequest $request, PickUp $pickup)
    {
        $this->authorize('pickups.edit');

        return $this->pickupRepository->updatePickup($request->all(), $pickup);
    }

    public function destroy(PickUp $pickup)
    {
        $this->authorize('pickups.delete');

        $this->pickupRepository->deletePickup($pickup);
    }

    public function assignDriver(AssignDriverRequest $request)
    {
        $this->authorize('pickups.assign driver');

        return $this->pickupRepository->assignDriverToPickups($request->all());
    }

    public function showPickupOrder(Request $request)
    {
        $this->authorize('pickups.show pickup order');

        return Inertia::render('Pickup/PickupOrder', [
            'filters' => $request->only('fromDate', 'toDate', 'driverId'),
            'drivers' => $this->driverRepository->getAllDrivers(),
            'pickups' => $this->pickupRepository->getFilteredPickups($request),
        ]);
    }

    public function updatePickupOrder(Request $request)
    {
        $this->authorize('pickups.update pickup order');

        if ($request->pickups) {
            $this->pickupRepository->savePickupOrder($request->pickups);
        }
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isUrgent', 'isImportant', 'createdBy', 'zoneBy']);

        return $this->pickupRepository->export($filters);
    }

    public function getPendingJobsByUser(string $user)
    {
        $this->authorize('pickups.pending pickups');

        return Inertia::render('Pickup/PendingJobsByUser', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'users' => $this->userRepository->getUsers(),
            'zones' => $this->zoneRepository->getZones(),
            'userData' => $user,
        ]);
    }

    public function allPickups()
    {
        $this->authorize('pickups.index');

        $pickups = $this->pickupRepository->getPickups();

        return Inertia::render('Pickup/AllPickups', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'users' => $this->userRepository->getUsers(['customer']),
            'zones' => $this->zoneRepository->getZones(),
            'pickups' => $pickups,
        ]);
    }

    public function allPickupsExport(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'isUrgent', 'isImportant', 'createdBy', 'zoneBy', 'driverBy', 'statusBy']);

        return $this->pickupRepository->exportDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function deletePickups(Request $request)
    {
        $this->authorize('pickups.delete');

        $this->pickupRepository->deletePickups($request->pickupIds);
    }

    public function getHBLStatusByPickup(PickUp $pickup)
    {
        $hbl = $pickup->hbl()->latest()->first();

        if ($hbl) {
            return $this->HBLRepository->getHBLStatus($hbl);
        }
    }

    public function unassignDriver(PickUp $pickup)
    {
        $this->authorize('pickups.unassign driver');

        $this->pickupRepository->unassignDriverFromPickup($pickup);
    }

    public function restore($pickUp)
    {
        return $this->pickupRepository->restorePickup($pickUp);
    }
}
