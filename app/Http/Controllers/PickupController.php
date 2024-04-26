<?php

namespace App\Http\Controllers;

use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupController extends Controller
{
    private PickupRepositoryInterface $pickupRepository;

    public function __construct(PickupRepositoryInterface $pickupRepository)
    {
        $this->pickupRepository = $pickupRepository;
    }

    public function index()
    {
        $pickups = PickUp::latest()->get();

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
