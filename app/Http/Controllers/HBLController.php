<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLPaymentStatus;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use App\Http\Requests\StoreHBLRequest;
use App\Http\Requests\UpdateHBLRequest;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\HBL;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PriceRepositoryInterface $priceRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('HBL/HBLList', [
            'users' => $this->userRepository->getUsers(),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
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

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('HBL/CreateHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => WarehouseType::cases(),
            'priceRules' => $this->priceRepository->getPriceRules(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHBLRequest $request)
    {
        $this->HBLRepository->storeHBL($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HBL $hbl)
    {
        return Inertia::render('HBL/EditHBL', [
            'hbl' => $hbl->load('packages'),
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => WarehouseType::cases(),
            'priceRules' => $this->priceRepository->getPriceRules(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHBLRequest $request, HBL $hbl)
    {
        $this->HBLRepository->updateHBL($request->all(), $hbl);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HBL $hbl)
    {
        $this->HBLRepository->deleteHBL($hbl);
    }

    public function toggleHold(HBL $hbl)
    {
        $this->HBLRepository->toggleHold($hbl);
    }

    public function downloadHBLPDF(HBL $hbl)
    {
        return $this->HBLRepository->downloadHBLPDF($hbl);
    }

    public function cancelledHBLs()
    {
        return Inertia::render('HBL/CancelledHBLList', [
            'users' => $this->userRepository->getUsers(),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
        ]);
    }

    public function cancelledList(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->HBLRepository->getCancelledList($limit, $page, $order, $dir, $search, $filters);
    }

    public function restore($id)
    {
        return $this->HBLRepository->restore($id);
    }

    public function downloadHBLInvoicePDF(HBL $hbl)
    {
        return $this->HBLRepository->downloadHBLInvoicePDF($hbl);
    }
}
