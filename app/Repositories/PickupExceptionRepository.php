<?php

namespace App\Repositories;

use App\Actions\PickUps\Exception\GetTotalPickupExceptionCount;
use App\Factory\Pickup\FilterFactory;
use App\Http\Resources\PickupExceptionResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Models\PickupException;

class PickupExceptionRepository implements GridJsInterface, PickupExceptionRepositoryInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PickupException::query();

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $exceptions = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = GetTotalPickupExceptionCount::run();

        return response()->json([
            'data' => PickupExceptionResource::collection($exceptions),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }
}
