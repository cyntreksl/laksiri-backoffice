<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignDriverRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupExceptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
        private readonly PickupExceptionRepositoryInterface $pickupExceptionRepository,
    ) {
    }

    public function index()
    {
        $this->authorize('pickups.show pickup exceptions');

        return Inertia::render('Pickup/Exceptions', [
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

        $filters = $request->only(['fromDate', 'toDate', 'createdBy', 'zoneBy']);

        return $this->pickupExceptionRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function assignDriver(AssignDriverRequest $request)
    {
        $this->authorize('pickups.assign driver');

        $this->pickupExceptionRepository->assignDriverToExceptions($request->all());
    }

    public function deleteExceptions(Request $request)
    {
        $this->authorize('pickups.delete');

        $this->pickupExceptionRepository->deleteExceptions($request->exceptionIds);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'createdBy', 'zoneBy']);

        return $this->pickupExceptionRepository->export($filters);
    }

    public function retry(PickUp $pickup)
    {
        $this->pickupExceptionRepository->retryException($pickup);
    }
}
