<?php

namespace App\Repositories;

use App\Factory\CashSettlement\FilterFactory;
use App\Http\Resources\CashSettlementCollection;
use App\Interfaces\CashSettlementInterface;
use App\Interfaces\GridJsInterface;
use App\Models\HBL;

class CashSettlementRepository implements CashSettlementInterface, GridJsInterface
{

    public function getPendingSettlementList(): HBL
    {
        // TODO: Implement getPendingSettlementList() method.
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', string $search = null, array $filters = [])
    {
        $query = HBL::query();
        $query->where('system_status',3.1);

        if (!empty($search)) {
            $query->where('hbl','like',"%$search%");
        }


        //apply filters
        FilterFactory::apply($query,$filters);

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = HBL::where('system_status',3.1)->count();

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
        $query->where('system_status',3.1);

        //apply filters
        FilterFactory::apply($query,$filters);

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
