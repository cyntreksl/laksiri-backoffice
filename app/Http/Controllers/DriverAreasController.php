<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverAreasRequest;
use App\Http\Resources\DriverAreasCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverAreasController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly DriverAreasRepositoryInterface $driverAreasRepositoryInterface,
    ) {
    }

    public function index()
    {
        return Inertia::render('Setting/DriverAreaList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $query = Area::with('Branch');

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $DriverAreas = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totaldriverares = Area::count();

        return response()->json([
            'data' => DriverAreasCollection::collection($DriverAreas),
            'meta' => [
                'total' => $totaldriverares,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totaldriverares / $limit),
            ],
        ]);
    }

    public function store(StoreDriverAreasRequest $request)
    {
        $this->driverAreasRepositoryInterface->createDriverAreas($request->all());
    }

    public function edit($id)
    {
        return Inertia::render('Setting/DriverAreasEdit', [
            'driverareas' => $this->driverAreasRepositoryInterface->getDriverAreas($id),
        ]);
    }

    public function update(StoreDriverAreasRequest $request)
    {
        $this->driverAreasRepositoryInterface->editDriverAreas($request->all());
    }

    public function delete($id)
    {
        $this->driverAreasRepositoryInterface->destroy(Area::find($id));
    }
}
