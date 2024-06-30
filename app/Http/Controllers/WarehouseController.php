<?php

namespace App\Http\Controllers;

use App\Actions\WarehouseZone\GetWarehouseZones;
use App\Enum\HBLPaymentStatus;
use App\Http\Requests\AssignZoneRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\HBL;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {
    }

    public function index()
    {
        $drivers = $this->driverRepository->getAllDrivers();
        $officers = [];

        return Inertia::render('Warehouse/WarehouseList', [
            'drivers' => $drivers,
            'officers' => $officers,
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouseZones' => GetWarehouseZones::run(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus']);

        return $this->warehouseRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function getSummery(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers']);

        return $this->warehouseRepository->getSummery($filters);
    }

    public function assignZone(AssignZoneRequest $request, HBL $hbl)
    {
        return $this->warehouseRepository->assignWarehouseZone($hbl, $request->warehouse_zone_id);
    }
}
