<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWareahouseZoneRequest;
use App\Http\Resources\WarehouseZoneCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseZoneController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly WarehousezoneRepositoryInterface $warehousezoneRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Setting/WarehouseZoneList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $query = WarehouseZone::with('Branch');

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $warehousezones = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalwzones = WarehouseZone::count();

        return response()->json([
            'data' => WarehouseZoneCollection::collection($warehousezones),
            'meta' => [
                'total' => $totalwzones,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalwzones / $limit),
            ],
        ]);
    }

    public function store(StoreWareahouseZoneRequest $request)
    {
        $this->warehousezoneRepository->createWarehouseZone($request->all());
    }

    public function edit($id)
    {
        return Inertia::render('Setting/WarehousezonesEdit', [
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
