<?php

namespace App\Http\Controllers;

use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupController extends Controller
{

    public function __construct(private readonly PickupRepositoryInterface $pickupRepository)
    {
    }

    public function index()
    {
        $pickups = $this->pickupRepository->getPickups();

        return Inertia::render('Pickup/PendingJobs', [
            'pickups' => $pickups
        ]);
    }

    public function create()
    {
        return Inertia::render('Pickup/CreateJob');
    }

    public function store(Request $request)
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
}
