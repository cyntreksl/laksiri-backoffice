<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PickupExceptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
        private readonly PickupExceptionRepositoryInterface $pickupExceptionRepository,
    ) {}

    public function index()
    {
        $this->authorize('pickups.show pickup exceptions');

        return response()->json([
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

        $data = $this->pickupExceptionRepository->dataset($limit, $page, $order, $dir, $search, $filters);

        return response()->json($data);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'createdBy', 'zoneBy']);

        $exportData = $this->pickupExceptionRepository->export($filters);

        return response()->json($exportData);
    }

    public function retry(PickUp $pickup)
    {
        $this->pickupExceptionRepository->retryException($pickup);

        return response()->json(['message' => 'Exception retry initiated']);
    }
}
