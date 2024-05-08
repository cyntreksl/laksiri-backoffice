<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Interfaces\ZoneRepositoryInterface;

class ZoneController extends Controller
{
    public function __construct(private readonly ZoneRepositoryInterface $zoneRepository)
    {
    }

    public function list()
    {
        $zones = $this->zoneRepository->getZones();

        return response()->json($zones);
    }
    public function store(StoreZoneRequest $request)
    {
        //        Expected Data Format
        //            $data = [
        //                'name' => 'Zone 2',
        //                'areas'=> [
        //                    ['name' => 'Area One'],
        //                    ['name' => 'Area 2'],
        //                    ['name' => 'Area 3'],
        //                ]
        //            ];
        $this->zoneRepository->store($request->all());

    }
}
