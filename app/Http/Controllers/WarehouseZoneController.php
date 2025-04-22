<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWareahouseZoneRequest;
use App\Http\Resources\WarehouseZoneCollection;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseZoneController extends Controller
{
    public function __construct(
        private readonly WarehousezoneRepositoryInterface $warehousezoneRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/WarehouseZone/WarehouseZoneList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $query = WarehouseZone::with('Branch');

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $warehousezones = $query->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => WarehouseZoneCollection::collection($warehousezones),
            'meta' => [
                'total' => $warehousezones->total(),
                'current_page' => $warehousezones->currentPage(),
                'perPage' => $warehousezones->perPage(),
                'lastPage' => $warehousezones->lastPage(),
            ],
        ]);
    }

    public function store(StoreWareahouseZoneRequest $request)
    {
        $this->warehousezoneRepository->createWarehouseZone($request->all());
    }

    public function edit($id)
    {
        return Inertia::render('Setting/WarehouseZone/WarehousezonesEdit', [
            'warehousezone' => $this->warehousezoneRepository->getWarehouseZone($id),
        ]);
    }

    public function update(StoreWareahouseZoneRequest $request)
    {
        $this->warehousezoneRepository->editWarehouseZone($request->all());
    }

    public function delete($id)
    {
        $this->warehousezoneRepository->destroy(WarehouseZone::find($id));
    }
}
