<?php

namespace App\Repositories;

use App\Actions\NoteType\GetNoteTypes;
use App\Actions\PickUps\AssignDriver;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\DeletePickup;
use App\Actions\PickUps\GetPickupByIds;
use App\Actions\PickUps\GetPickups;
use App\Actions\PickUps\SavePickUpOrder;
use App\Actions\PickUps\UnassignDriver;
use App\Actions\PickUps\UpdatePickUp;
use App\Events\PickupDriverAssigned;
use App\Exports\PickupsExport;
use App\Factory\Pickup\FilterFactory;
use App\Http\Resources\PickupResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PickupRepository implements GridJsInterface, PickupRepositoryInterface
{
    public function getPickups()
    {
        return GetPickups::run();
    }

    public function storePickup(array $data)
    {
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

            PickupDriverAssigned::dispatch($pickup);
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        if (isset($filters['userData'])) {
            $query = PickUp::query()
                ->where('name', $filters['userData'])
                ->orWhere('contact_number', $filters['userData'])
                ->with('pickupException')
                ->whereIn('system_status', [
                    PickUp::SYSTEM_STATUS_PICKUP_CREATED,
                    PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
                ])->whereDoesntHave('pickupException');
        } else {
            $query = PickUp::query()
                ->with('pickupException')
                ->whereIn('system_status', [
                    PickUp::SYSTEM_STATUS_PICKUP_CREATED,
                    PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
                ])->whereDoesntHave('pickupException');
        }

        if ($filters['view'] == 'trashed') {
            $query->onlyTrashed();
        }

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            });
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $query->orderBy('created_at', 'desc');

        $pickups = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PickupResource::collection($pickups),
            'meta' => [
                'total' => $pickups->total(),
                'current_page' => $pickups->currentPage(),
                'perPage' => $pickups->perPage(),
                'lastPage' => $pickups->lastPage(),
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
            ->whereIn('system_status', [
                PickUp::SYSTEM_STATUS_PICKUP_CREATED,
                PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
            ])
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

    public function deletePickup(PickUp $pickup, ?string $deleteRemarks = null, ?string $deleteMainReason = null)
    {
        return DeletePickup::run($pickup, $deleteRemarks, $deleteMainReason);
    }

    public function deletePickups(array $pickupIds, ?string $deleteRemarks = null, ?string $deleteMainReason = null)
    {
        $pickupList = GetPickupByIds::run($pickupIds);

        foreach ($pickupList as $pickup) {
            DeletePickup::run($pickup, $deleteRemarks, $deleteMainReason);
        }
    }

    public function export(array $filters)
    {
        return Excel::download(new PickupsExport($filters), 'pickups.xlsx');
    }

    public function exportDataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        if (isset($filters['userData'])) {
            $query = PickUp::query()
                ->where('name', $filters['userData'])
                ->orWhere('contact_number', $filters['userData'])
                ->with('pickupException')
                ->whereIn('system_status', [
                    PickUp::SYSTEM_STATUS_PICKUP_CREATED,
                    PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
                ]);
        } else {
            $query = PickUp::query()
                ->with('pickupException')
                ->whereIn('system_status', [
                    PickUp::SYSTEM_STATUS_PICKUP_CREATED,
                    PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
                ]);
        }

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            });
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $pickups = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PickupResource::collection($pickups),
            'meta' => [
                'total' => $pickups->total(),
                'current_page' => $pickups->currentPage(),
                'perPage' => $pickups->perPage(),
                'lastPage' => $pickups->lastPage(),
            ],
        ]);
    }

    public function unassignDriverFromPickup(PickUp $pickup): void
    {
        UnassignDriver::run($pickup);
    }

    public function restorePickup($pickUp)
    {
        $pickUp = PickUp::withTrashed()->find($pickUp);

        $pickUp->restore();
    }
}
