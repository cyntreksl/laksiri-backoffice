<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Resources\ZoneCollection;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneController extends Controller
{
    public function __construct(
        private readonly ZoneRepositoryInterface $zoneRepository,
        private readonly DriverAreasRepositoryInterface $driverAreasRepositoryInterface,
    ) {}

    public function index()
    {

        return Inertia::render('Setting/DriverZone/DriverZoneList', [
            'areas' => $this->driverAreasRepositoryInterface->getAreas(),
        ]);

    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $query = Zone::query()->with('areas');

        if (! empty($search)) {
            $query->searchByZoneOrArea($search);
        }

        $zones = $query->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => ZoneCollection::collection($zones),
            'meta' => [
                'total' => $zones->total(),
                'current_page' => $zones->currentPage(),
                'perPage' => $zones->perPage(),
                'lastPage' => $zones->lastPage(),
            ],
        ]);
    }

    public function store(StoreZoneRequest $request)
    {
        $this->zoneRepository->store($request->all());
    }

    public function destroy(Zone $zone)
    {
        $this->zoneRepository->destroy($zone);
    }
}
