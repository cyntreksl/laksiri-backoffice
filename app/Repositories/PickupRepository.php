<?php

namespace App\Repositories;

use App\Actions\NoteType\GetNoteTypes;
use App\Actions\PickUps\AssignDriver;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\GetPickupByIds;
use App\Actions\PickUps\GetPickups;
use App\Actions\PickUps\SavePickUpOrder;
use App\Actions\PickUps\UpdatePickUp;
use App\Factory\Pickup\FilterFactory;
use App\Http\Resources\PickupResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;

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

    public function assignDriverToPickups(array $data): void
    {
        $pickupList = GetPickupByIds::run($data['job_ids']);

        foreach ($pickupList as $pickup) {
            AssignDriver::run($pickup, $data['driver_id']);
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PickUp::query()->whereIn('system_status', [1, 2]);

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            });
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;

        $pickups = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

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
            FilterFactory::apply($query, ['FromPickupDate' => $request->fromDate, 'toPickupDate' => $request->toDate, 'driverBy' => $request->driverId]);
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

    public function updatePickup(array $data, PickUp $pickup)
    {
        return UpdatePickUp::run($data, $pickup);
    }
}
