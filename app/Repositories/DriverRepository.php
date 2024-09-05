<?php

namespace App\Repositories;

use App\Actions\Driver\DeleteDriver;
use App\Actions\Driver\GetDrivers;
use App\Actions\Driver\UpdateDriverDetails;
use App\Actions\Driver\UpdateDriverPassword;
use App\Actions\User\CreateUser;
use App\Actions\Zone\CreateZone;
use App\Exports\DriversExport;
use App\Factory\User\FilterFactory;
use App\Http\Resources\DriverCollection;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class DriverRepository implements DriverRepositoryInterface, GridJsInterface
{
    public function getAllDrivers(): Collection
    {
        return GetDrivers::run();
    }

    public function storeDriver(array $data)
    {
        // Check and create zones if they don't exist
        if (! empty($data['preferred_zone'])) {
            $mappedZones = array_map(fn ($zone) => ['name' => $zone], $data['preferred_zone']);
            foreach ($mappedZones as $zoneData) {
                CreateZone::run($zoneData);
            }
        }

        return CreateUser::run($data);
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = User::role('driver')->currentBranch();

        if (! empty($search)) {
            $query->where('username', 'like', '%'.$search.'%');
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $users = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => DriverCollection::collection($users),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);

    }

    public function updateDriverDetails(array $data, User $user): void
    {

        UpdateDriverDetails::run($data, $user);

    }

    public function updateDriverPassword(array $data, User $user): void
    {
        UpdateDriverPassword::run($data, $user);
    }

    public function deleteDriver(User $user): void
    {
        DeleteDriver::run($user);
    }

    public function export(array $filters)
    {
        return Excel::download(new DriversExport($filters), 'drivers.xlsx');
    }
}
