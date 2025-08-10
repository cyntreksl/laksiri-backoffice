<?php

namespace App\Repositories;

use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Actions\HBL\Warehouse\AssignZone;
use App\Actions\HBL\Warehouse\GetWarehouseByIds;
use App\Exports\WarehouseExport;
use App\Factory\Warehouse\FilterFactory;
use App\Http\Resources\CashSettlementCollection;
use App\Interfaces\GridJsInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseRepository implements GridJsInterface, WarehouseRepositoryInterface
{
    use ResponseAPI;

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        $query->warehouse();

        if (! empty($search)) {
            $query->where('hbl_number', 'like', "%$search%");
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => CashSettlementCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }

    public function getSummery(array $filters = [])
    {
        $query = HBL::query();

        $query->warehouse();

        $filters['isHold'] = isset($filters['isHold']) ? (bool) $filters['isHold'] : false;

        // apply filters
        FilterFactory::apply($query, $filters);

        $records = $query
            ->withSum('packages', 'actual_weight')
            ->withSum('packages', 'volume')
            ->withSum('packages', 'quantity')
            ->get();

        $sumAmount = $records->sum('grand_total');
        $sumPaidAmount = $records->sum('paid_amount');
        $countRecords = $records->count();
        $sumWeight = $records->sum('packages_sum_actual_weight');
        $sumVolume = $records->sum('packages_sum_volume');
        $sumQuantity = $records->sum('packages_sum_quantity');

        return [
            'totalRecords' => $countRecords,
            'sumAmount' => $sumAmount,
            'sumPaidAmount' => $sumPaidAmount,
            'sumWeight' => $sumWeight,
            'sumVolume' => $sumVolume,
            'sumQuantity' => $sumQuantity,
        ];
    }

    public function assignWarehouseZone(HBL $hbl, int $warehouse_zone_id)
    {
        return AssignZone::run($hbl, $warehouse_zone_id);
    }

    public function export(array $filters)
    {
        return Excel::download(new WarehouseExport($filters), 'warehouse.xlsx');
    }

    public function revertToCashSettlement(array $hblIds)
    {
        $hblList = GetWarehouseByIds::run($hblIds);

        foreach ($hblList as $hbl) {
            if ($hbl->pickup_id) {
                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER);
            } else {
                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE);
            }

            $hbl = HBL::find($hbl->id);
            $hbl->addStatus('Revert To Cash Settlement');
        }

        return $this->success('Revert to Cash Settlement', []);
    }
}
