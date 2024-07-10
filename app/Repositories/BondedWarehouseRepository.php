<?php

namespace App\Repositories;

use App\Actions\HBL\MarkAsShortLoading;
use App\Factory\BondedWarehouse\FilterFactory;
use App\Http\Resources\BondedWarehouseCollection;
use App\Interfaces\BondedWarehouseRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class BondedWarehouseRepository implements BondedWarehouseRepositoryInterface, GridJsInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        $query->withoutGlobalScope(BranchScope::class);

        $query->whereIn('system_status', [4.3, 4.4]);

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
            'data' => BondedWarehouseCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function markAsShortLoading($hbl_id)
    {
        $hbl = HBL::withoutGlobalScope(BranchScope::class)
            ->find($hbl_id);

        MarkAsShortLoading::run($hbl);
    }
}
