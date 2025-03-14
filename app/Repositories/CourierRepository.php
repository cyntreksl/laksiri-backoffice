<?php

namespace App\Repositories;

use App\Actions\Courier\CreateCourier;
use App\Actions\Courier\CreateCourierPackages;
use App\Actions\Courier\DeleteCourier;
use App\Factory\Courier\FilterFactory;
use App\Http\Resources\CourierCollection;
use App\Interfaces\CourierRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Courier;

class CourierRepository implements CourierRepositoryInterface, GridJsInterface
{
    public function storeCourier(array $data)
    {
        $data['status'] = Courier::PENDING;
        $courier = CreateCourier::run($data);
        $packagesData = $data['packages'];
        CreateCourierPackages::run($courier, $packagesData);
        $courier->addStatus('Courier Created');

        return $courier;

    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Courier::query();

        if (! empty($search)) {
            $query->where('courier_number', 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $users = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => CourierCollection::collection($users),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);

    }

    public function deleteCourier(Courier $courier)
    {
        DeleteCourier::run($courier);
    }
}
