<?php

namespace App\Http\Controllers;

use App\Actions\HBL\GetHBLByIdWithPackages;
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
use App\Models\HBLDocument;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    use AuthorizesRequests;

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
        $this->authorize('hbls.index');

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
        $this->authorize('hbls.create');

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

    public function show($hbl_id)
    {
        return response()->json([
            'hbl' => GetHBLByIdWithPackages::run($hbl_id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HBL $hbl)
    {
        $this->authorize('hbls.edit');

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
        $this->authorize('hbls.delete');

        $this->HBLRepository->deleteHBL($hbl);
    }

    public function toggleHold(HBL $hbl)
    {
        $this->authorize('hbls.hold and release');

        $this->HBLRepository->toggleHold($hbl);
    }

    public function downloadHBLPDF(HBL $hbl)
    {
        $this->authorize('hbls.download pdf');

        return $this->HBLRepository->downloadHBLPDF($hbl);
    }

    public function cancelledHBLs()
    {
        $this->authorize('hbls.show cancelled hbls');

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
        $this->authorize('hbls.restore');

        return $this->HBLRepository->restore($id);
    }

    public function downloadHBLInvoicePDF(HBL $hbl)
    {
        $this->authorize('hbls.download invoice');

        return $this->HBLRepository->downloadHBLInvoicePDF($hbl);
    }

    public function downloadHBLBarcodePDF(HBL $hbl)
    {
        $this->authorize('hbls.download barcode');

        return $this->HBLRepository->downloadHBLBarcodePDF($hbl);
    }

    public function getHBLByPackageId($hbl_package_id)
    {
        return $this->HBLRepository->getHBLByPackageId($hbl_package_id);
    }

    public function uploadDocument(Request $request)
    {
        $this->authorize('hbls.upload documents');

        return $this->HBLRepository->uploadDocument($request->all());
    }

    public function getHBLDocuments(HBL $hbl)
    {
        return response()->json($hbl->hblDocuments);
    }

    public function destroyHBLDocument(HBLDocument $hblDocument)
    {
        $this->authorize('hbls.delete documents');

        $this->HBLRepository->deleteDocument($hblDocument);
    }

    public function getPickupStatus(HBL $hbl)
    {
        return $this->HBLRepository->getPickupStatus($hbl);
    }

    public function getHBLStatus(HBL $hbl)
    {
        return $this->HBLRepository->getHBLStatus($hbl);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->HBLRepository->export($filters);
    }

    public function exportCancelled(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->HBLRepository->exportCancelled($filters);
    }

    public function getHBLByReference($reference = null)
    {
        return $this->HBLRepository->getHBLByReference($reference);
    }

    public function getHBLPackagesByReference($reference = null)
    {
        return $this->HBLRepository->getHBLPackagesByReference($reference);
    }

    public function getHBLStatusByReference($reference = null)
    {
        return $this->HBLRepository->getHBLStatusByReference($reference);
    }

    public function getHBLLogs(HBL $hbl)
    {
        return response()->json($hbl->activities()->with('causer')->get());
    }

    public function createToken(HBL $hbl)
    {
        return $this->HBLRepository->createAndIssueToken($hbl);
    }
}
