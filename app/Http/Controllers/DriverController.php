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

    public function store(StoreDriverRequest $request)
    {
        $this->driverRepository->storeDriver($request->all());
    }

    public function show(User $user)
    {
    }

    public function edit(User $user)
    {
    }

    public function update(Request $request, User $user)
    {
    }

    public function destroy(User $user)
    {
    }
}
