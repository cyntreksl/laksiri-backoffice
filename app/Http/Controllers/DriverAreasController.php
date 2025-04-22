<?php

namespace App\Http\Controllers;

use App\Actions\Zone\GetZones;
use App\Http\Requests\StoreDriverAreasRequest;
use App\Http\Resources\DriverAreasCollection;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverAreasController extends Controller
{
    public function __construct(
        private readonly DriverAreasRepositoryInterface $driverAreasRepositoryInterface,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/DriverAreas/DriverAreaList', [
            'zones' => GetZones::run(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $query = Area::with('Branch');

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $DriverAreas = $query->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => DriverAreasCollection::collection($DriverAreas),
            'meta' => [
                'total' => $DriverAreas->total(),
                'current_page' => $DriverAreas->currentPage(),
                'perPage' => $DriverAreas->perPage(),
                'lastPage' => $DriverAreas->lastPage(),
            ],
        ]);
    }

    public function store(StoreDriverAreasRequest $request)
    {
        $this->driverAreasRepositoryInterface->createDriverAreas($request->all());
    }

    public function update(StoreDriverAreasRequest $request)
    {
        $this->driverAreasRepositoryInterface->editDriverAreas($request->all());
    }

    public function destroy($id)
    {
        $this->driverAreasRepositoryInterface->destroy(Area::find($id));
    }
}
