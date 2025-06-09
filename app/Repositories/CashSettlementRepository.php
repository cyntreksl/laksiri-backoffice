<?php

namespace App\Repositories;

use App\Actions\HBL\CashSettlement\GetCashSettlementByIds;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Events\PickupCollected;
use App\Exports\CashSettlementsExport;
use App\Exports\DuePaymentExport;
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
            $query->where('hbl_number', 'like', "%$search%");
        }

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

    public function duePaymentDataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        $query->duePayment()->whereHas('packages');

        if (! empty($search)) {
            $query->where('hbl_number', 'like', "%$search%");
        }

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

    public function getSummery(array $filters = []): array
    {
        $query = HBL::query();

        $query->cashSettlement()->whereHas('packages');

        // Ensure 'isHold' key exists and set to boolean
        $filters['isHold'] = isset($filters['isHold']) ? (bool) $filters['isHold'] : false;

        // Apply filters
        FilterFactory::apply($query, $filters);

        // Fetch records
        $records = $query->get();

        return [
            'totalRecords' => $records->count(),
            'sumAmount' => $records->sum('grand_total'),
            'sumPaidAmount' => $records->sum('paid_amount'),
        ];
    }

    public function cashReceived(array $hblIds)
    {
        $hblList = GetCashSettlementByIds::run($hblIds);

        foreach ($hblList as $hbl) {
            UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_CASH_RECEIVED);
            $hbl = HBL::find($hbl->id);
            $hbl->addStatus('Cash Received by Accountant');

            PickupCollected::dispatch($hbl);
        }

        return $this->success('Cash Received', []);
    }

    public function updatePayment(array $data, HBL $hbl)
    {
        $new_paid_amount = $data['paid_amount'];
        $old_paid_amount = $hbl->paid_amount;
        $total_paid_amount = $old_paid_amount + $new_paid_amount;

        $paymentData = array_merge($data, [
            'paid_amount' => $total_paid_amount,
        ]);

        UpdateHBLPayments::run($paymentData, $hbl);
    }

    public function export(array $filters)
    {
        return Excel::download(new CashSettlementsExport($filters), 'cash-settlements.xlsx');
    }

    public function duePaymentExport(array $filters)
    {
        return Excel::download(new DuePaymentExport($filters), 'due-payment.xlsx');
    }
}
