<?php

namespace App\Repositories;

use App\Actions\Driver\GetDrivers;
use App\Actions\User\CreateUser;
use App\Factory\User\FilterFactory;
use App\Http\Resources\DriverCollection;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DriverRepository implements DriverRepositoryInterface, GridJsInterface
{
    public function getAllDrivers(): Collection
    {
        return GetDrivers::run();
    }

    public function storeDriver(array $data)
    {
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

        $users = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

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
}
