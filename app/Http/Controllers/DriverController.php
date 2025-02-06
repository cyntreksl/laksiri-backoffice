<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverDetailsRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
    ) {
    }

    public function index()
    {
        $this->authorize('users.list');

        return Inertia::render('Driver/DriverList', [
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

        $filters = $request->only(['fromDate', 'toDate']);

        return $this->driverRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function store(StoreDriverRequest $request)
    {
        $this->driverRepository->storeDriver($request->all());
    }

    public function edit($id)
    {
        $this->authorize('users.edit');

        $user = User::find($id);

        return Inertia::render('Driver/DriverEdit', [
            'driver' => $user,
            'zones' => $this->zoneRepository->getZones(),
        ]);

    }

    public function update(Request $request, User $user)
    {
    }

    public function changeDriverBasicDetails(UpdateDriverDetailsRequest $request, User $user)
    {

        $this->driverRepository->updateDriverDetails($request->all(), $user);

    }

    public function changeDriverPassword(UpdateUserPasswordRequest $request, User $user)
    {
        $this->driverRepository->updateDriverPassword($request->all(), $user);

    }

    public function destroy($id)
    {
        $this->authorize('users.delete');

        $this->driverRepository->deleteDriver(User::find($id));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate']);

        return $this->driverRepository->export($filters);
    }
}
