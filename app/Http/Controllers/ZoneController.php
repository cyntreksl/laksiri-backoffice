<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Resources\ZoneCollection;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneController extends Controller
{
    public function __construct(private readonly ZoneRepositoryInterface $zoneRepository)
    {
    }

    public function index()
    {
        return Inertia::render('Settings/Zone/ZoneList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 0);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'ASC');
        $search = $request->input('search', null);

        $query = Zone::query()->with('areas');

        if (!empty($search)) {
            $query->searchByZoneOrArea($search);
        }

        $zones = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalZones = Zone::count();

        return response()->json([
            'data' => ZoneCollection::collection($zones),
            'meta' => [
                'total' => $totalZones,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalZones / $limit),
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
