<?php

namespace App\Http\Controllers;

use App\Enum\HBLPaymentStatus;
use App\Interfaces\DriverRepositoryInterface;
use App\Models\HBL;
use App\Repositories\CashSettlementRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSettlementController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly CashSettlementRepository $cashSettlementRepository,
    ) {
    }

    public function index()
    {
        $drivers = $this->driverRepository->getAllDrivers();
        $officers = [];

        return Inertia::render('CashSettlement/CashSettlementList', [
            'drivers' => $drivers,
            'officers' => $officers,
            'paymentStatus' => HBLPaymentStatus::cases(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus']);

        return $this->cashSettlementRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function getSummery(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'deliveryType', 'upb', 'd2d', 'gift', 'drivers', 'officers']);

        return $this->cashSettlementRepository->getSummery($filters);
    }

    public function cashReceived(Request $request)
    {
        $hblIds = $request->hbl_ids;

        return $this->cashSettlementRepository->cashReceived($hblIds);
    }

    public function paymentUpdate(Request $request, HBL $hbl)
    {
        $this->cashSettlementRepository->updatePayment($request->all(), $hbl);
    }
}
