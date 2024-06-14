<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWareahouseZoneRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\WarehouseZoneCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\WarehousezoneRepositoryInterface;
use App\Models\User;
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
        return Inertia::render('Setting/WarehouseZoneList', [
            'branches' => $this->branchRepository->getBranches(),
        ]);
    }

    public function store(StoreWareahouseZoneRequest $request)
    {
        $this->warehousezoneRepository->createWarehouseZone($request->all());
    }

    public function delete($id)
    {
        $this->warehousezoneRepository->destroy(WarehouseZone::find($id));
    }

    public function list(Request $request)
    {
        // dd(WarehouseZone::with('branch')->find(2)->branch_name);
        // dd(UserCollection::collection(User::all()));

        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $query = WarehouseZone::with('Branch');

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $wzones = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalwzones = WarehouseZone::count();

        return response()->json([
            'data' => WarehouseZoneCollection::collection($wzones),
            'meta' => [
                'total' => $totalwzones,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalwzones / $limit),
            ],
        ]);
    }
}
