<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePickupTypeRequest;
use App\Http\Requests\UpdatePickupTypeRequest;
use App\Interfaces\PickupTypeRepositoryInterface;
use App\Models\PickupType;
use Inertia\Inertia;

class PickupTypeController extends Controller
{
    public function __construct(
        private readonly PickupTypeRepositoryInterface $pickupTypeRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/PickupTypes/PickupTypeList', [
            'pickupTypes' => $this->pickupTypeRepository->getPickupTypes(),
        ]);
    }

    public function store(StorePickupTypeRequest $request)
    {
        $this->pickupTypeRepository->storePickupType($request->all());
    }

    public function destroy(PickupType $pickupType)
    {
        $this->pickupTypeRepository->destroyPickupType($pickupType);
    }

    public function update(UpdatePickupTypeRequest $request, PickupType $pickupType)
    {
        $this->pickupTypeRepository->updatePickupType($pickupType, $request->all());
    }
}
