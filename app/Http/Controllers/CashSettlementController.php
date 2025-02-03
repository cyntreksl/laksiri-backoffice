<?php

namespace App\Http\Controllers;

use App\Enum\HBLPaymentStatus;
use App\Interfaces\DriverRepositoryInterface;
use App\Models\HBL;
use App\Repositories\CashSettlementRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSettlementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly CashSettlementRepository $cashSettlementRepository,
    ) {}

    public function index()
    {
        $this->authorize('cash.index');

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
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'deliveryType', 'upb', 'd2d', 'gift', 'drivers', 'officers', 'paymentStatus']);

        return $this->cashSettlementRepository->getSummery($filters);
    }

    public function cashReceived(Request $request)
    {
        $this->authorize('cash.cash received');

        $hblIds = $request->hbl_ids;

        return $this->cashSettlementRepository->cashReceived($hblIds);
    }

    public function paymentUpdate(Request $request, HBL $hbl)
    {
        $this->authorize('cash.update payment');

        $this->cashSettlementRepository->updatePayment($request->all(), $hbl);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus']);

        return $this->cashSettlementRepository->export($filters);
    }

    public function duePaymentIndex()
    {
        $this->authorize('cash.index');

        $drivers = $this->driverRepository->getAllDrivers();
        $officers = [];

        return Inertia::render('DuePayment/DuePaymentList', [
            'drivers' => $drivers,
            'officers' => $officers,
            'paymentStatus' => HBLPaymentStatus::cases(),
        ]);
    }

    public function duePaymentList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus']);

        return $this->cashSettlementRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }
}
