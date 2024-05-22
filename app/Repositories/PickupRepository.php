<?php

namespace App\Repositories;

use App\Actions\NoteType\GetNoteTypes;
use App\Actions\PickUps\AssignDriver;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\GetPickups;
use App\Actions\PickUps\GetTotalPickupCount;
use App\Factory\Pickup\FilterFactory;
use App\Http\Resources\PickupResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;

class PickupRepository implements GridJsInterface, PickupRepositoryInterface
{
    public function getPickups()
    {
        return GetPickups::run();
    }

    public function storePickup(array $data)
    {
        // assign location longitude, latitude and name

        // store pickup
        return CreatePickUp::run($data);
    }

    public function getNoteTypes()
    {
        return GetNoteTypes::run();
    }

    public function assignDriver(array $data, PickUp $pickUp)
    {
        return AssignDriver::run($data, $pickUp);
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PickUp::query();

        if (!empty($search)) {
            $query->whereAny(['reference', 'name', 'contact_number'], 'like', '%' . $search . '%');
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $pickups = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = GetTotalPickupCount::run();

        return response()->json([
            'data' => PickupResource::collection($pickups),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function getFilteredPickups(Request $request)
    {
        $query = Pickup::query();
        if ($request->filled('fromDate') || $request->filled('toDate') || $request->filled('driverId')) {
            FilterFactory::apply($query, ['fromDate' => $request->fromDate, 'toDate' => $request->toDate, 'driverId' => $request->driverId]);
        } else {
            // If no filters are provided, return an empty collection
            $query->whereRaw('1 = 0');
        }

        return $query
            ->with('zone')
            ->orderBy('pickup_order')
            ->get();
    }

    public function savePickupOrder(array $pickups): void
    {
        foreach ($pickups as $item) {
            SavePickUpOrder::run($item);
        }
    }
}
