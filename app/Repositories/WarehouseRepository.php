<?php

namespace App\Repositories;

use App\Factory\Warehouse\FilterFactory;
use App\Http\Resources\CashSettlementCollection;
use App\Interfaces\GridJsInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;

class WarehouseRepository implements GridJsInterface, WarehouseRepositoryInterface
{
    use ResponseAPI;

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        $query->warehouse();

        if (! empty($search)) {
            $query->where('hbl', 'like', "%$search%");
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

        return response()->json([
            'data' => CashSettlementCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function getSummery(array $filters = [])
    {
        $query = HBL::query();
        $query->warehouse();

        //apply filters
        FilterFactory::apply($query, $filters);

        $records = $query->get();

        $sumAmount = $records->sum('grand_total');
        $sumPaidAmount = $records->sum('paid_amount');
        $countRecords = $records->count();

        return [
            'totalRecords' => $countRecords,
            'sumAmount' => $sumAmount,
            'sumPaidAmount' => $sumPaidAmount,
        ];
    }
}
