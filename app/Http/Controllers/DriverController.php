<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Driver/DriverList');
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

    public function edit(User $user)
    {
    }

    public function update(Request $request, User $user)
    {
    }
}
