<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Interfaces\DriverRepositoryInterface;
use App\Repositories\CashSettlementRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSettlementController extends Controller
{

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly CashSettlementRepository $cashSettlementRepository,
    )
    {
    }

    public function index()
    {
        $drivers = $this->driverRepository->getAllDrivers();
        $officers = [];
        return Inertia::render('CashSettlement/CashSettlementList', ['drivers' => $drivers, 'officers' => $officers]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode','deliveryType','upb', 'd2d', 'gift', 'drivers', 'officers']);

       return $this->cashSettlementRepository->dataset($limit, $page, $order, $dir, $search,$filters);
    }
}
