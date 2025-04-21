<?php

namespace App\Repositories;

use App\Factory\User\FilterFactory;
use App\Http\Resources\DriverCollection;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\User;

class CustomerRepository implements CustomerRepositoryInterface, GridJsInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = User::role('customer')->currentBranch();

        if (! empty($search)) {
            $query->where('username', 'like', '%'.$search.'%')->orWhere('name', 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $users = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => DriverCollection::collection($users),
            'meta' => [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'lastPage' => $users->lastPage(),
            ],
        ]);
    }
}
