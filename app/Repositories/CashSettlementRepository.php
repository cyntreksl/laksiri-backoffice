<?php

namespace App\Repositories;

use App\Actions\HBL\CashSettlement\GetCashSettlementByIds;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Exports\CashSettlementsExport;
use App\Factory\CashSettlement\FilterFactory;
use App\Http\Resources\CashSettlementCollection;
use App\Interfaces\CashSettlementInterface;
use App\Interfaces\GridJsInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Maatwebsite\Excel\Facades\Excel;

class CashSettlementRepository implements CashSettlementInterface, GridJsInterface
{
    use ResponseAPI;

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        $query->cashSettlement()->whereHas('packages');

        if (! empty($search)) {
            $query->where('hbl', 'like', "%$search%");
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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
        $query->cashSettlement()->whereHas('packages');

        // apply filters
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

    public function cashReceived(array $hblIds)
    {
        $hblList = GetCashSettlementByIds::run($hblIds);

        foreach ($hblList as $hbl) {
            UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_CASH_RECEIVED);
            $hbl = HBL::find($hbl->id);
            $hbl->addStatus('Cash Received by Accountant');
        }

        return $this->success('Cash Received', []);
    }

    public function updatePayment(array $data, HBL $hbl)
    {
        $new_paid_amount = $data['paid_amount'];
        $old_paid_amount = $hbl->paid_amount;
        $total_paid_amount = $old_paid_amount + $new_paid_amount;

        UpdateHBLPayments::run($total_paid_amount, $hbl);
    }

    public function export(array $filters)
    {
        return Excel::download(new CashSettlementsExport($filters), 'cash-settlements.xlsx');
    }
}
