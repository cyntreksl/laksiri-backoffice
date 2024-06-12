<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly ZoneRepositoryInterface $zoneRepository,
    ) {
    }

    public function index()
    {
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
        $user = User::find($id);

        return Inertia::render('Driver/DriverEdit', [
            'user' => $user,
        ]);

    }

    public function update(Request $request, User $user)
    {
    }
}
