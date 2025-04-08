<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePickupTypeRequest;
use App\Http\Requests\UpdatePickupTypeRequest;
use App\Interfaces\PickupTypeRepositoryInterface;
use App\Models\PickupType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PickupTypeController extends Controller
{
    public function __construct(
        private readonly PickupTypeRepositoryInterface $pickupTypeRepository,
    ) {}

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');

        return $this->pickupTypeRepository->dataset($limit, $page, $order, $dir);
    }

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
