<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\WarehouseZone\GetWarehouseZones;
use App\Enum\HBLPaymentStatus;
use App\Http\Requests\AssignZoneRequest;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\HBL;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function index()
    {
        $this->authorize('warehouse.index');

        $drivers = $this->driverRepository->getAllDrivers();

        return Inertia::render('Warehouse/WarehouseList', [
            'drivers' => $drivers,
            'officers' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouseZones' => GetWarehouseZones::run(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->warehouseRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function getSummery(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->warehouseRepository->getSummery($filters);
    }

    public function assignZone(AssignZoneRequest $request, HBL $hbl)
    {
        $this->authorize('warehouse.assign zone');

        return $this->warehouseRepository->assignWarehouseZone($hbl, $request->warehouse_zone_id);
    }

    public function export(Request $request)
    {
        $filteredRequest = array_filter($request->all(), fn ($value) => $value !== 'null' && $value !== null);
        $filters = collect($filteredRequest)->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse'])->toArray();

        return $this->warehouseRepository->export($filters);
    }

    public function downloadBarcode(HBL $hbl)
    {
        $this->authorize('warehouse.download barcode');

        return $this->HBLRepository->downloadHBLBarcodePDF($hbl);
    }

    public function revertToCashSettlement(Request $request)
    {
        $this->authorize('warehouse.revert to cash settlement');

        $hblIds = $request->hbl_ids;

        return $this->warehouseRepository->revertToCashSettlement($hblIds);
    }
}
