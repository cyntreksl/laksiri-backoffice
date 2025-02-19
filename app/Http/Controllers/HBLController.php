<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLById;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\HBL\GetHBLWithTrashedById;
use App\Enum\CargoType;
use App\Enum\HBLPaymentStatus;
use App\Enum\HBLType;
use App\Http\Requests\StoreCallFlagRequest;
use App\Http\Requests\StoreHBLRequest;
use App\Http\Requests\UpdateHBLRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\SettingRepositoryInterface;
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
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('hbls.index');

        return Inertia::render('HBL/HBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
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

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus']);

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
            'warehouses' => GetDestinationBranches::run(),
            'priceRules' => $this->priceRepository->getPriceRules(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHBLRequest $request)
    {
        $hbl = $this->HBLRepository->storeHBL($request->all());

        return Inertia::render('HBL/CreateHBL', [
            'success' => 'HBL Created Successfully!',
            'hbl_id' => $hbl->id,
        ]);
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
            'warehouses' => GetDestinationBranches::run(),
            'priceRules' => $this->priceRepository->getPriceRules(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
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

    public function downloadHBLPDF($HBL)
    {
        $hbl = GetHBLByIdWithPackages::run($HBL);
        $this->authorize('hbls.download pdf');

        return $this->HBLRepository->downloadHBLPDF($hbl);
    }

    public function downloadCancelledHBLPDF($HBL)
    {
        $hbl = GetHBLWithTrashedById::run($HBL);
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

    public function getHBLDocuments($HBL)
    {
        $hbl = GetHBLById::run($HBL);

        return response()->json($hbl->hblDocuments);
    }

    public function destroyHBLDocument(HBLDocument $hblDocument)
    {
        $this->authorize('hbls.delete documents');

        $this->HBLRepository->deleteDocument($hblDocument);
    }

    public function getPickupStatus($id)
    {
        return $this->HBLRepository->getPickupStatus($id);
    }

    public function getHBLStatus($HBL)
    {
        $hbl = GetHBLById::run($HBL);

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

    public function showTracking(Request $request)
    {
        return Inertia::render('Tracking', [
            'reference' => $request->all()['hbl'] ?? null,
        ]);
    }

    public function getHBLLogs($HBL)
    {
        $hbl = GetHBLByIdWithPackages::run($HBL);

        return response()->json($hbl->activities()->with('causer')->withoutGlobalScopes()->get());
    }

    public function getHBLsByUser(string $user)
    {
        $this->authorize('hbls.index');

        return Inertia::render('HBL/HBLListByUser', [
            'users' => $this->userRepository->getUsers(),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'userData' => $user,
        ]);
    }

    public function downloadDocument(HBLDocument $hbl_document)
    {
        $this->authorize('hbls.download pdf');

        return $this->HBLRepository->downloadDocument($hbl_document);
    }

    public function calculatePayment(Request $request)
    {
        return $this->HBLRepository->calculatePayment($request->all());
    }

    public function showDraftList()
    {
        $this->authorize('hbls.show draft hbls');

        return Inertia::render('HBL/HBLDraftList', [
            'users' => $this->userRepository->getUsers(),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
        ]);
    }

    public function getDraftList(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        return $this->HBLRepository->getDraftList($limit, $page, $order, $dir, $search);
    }

    public function createCallFlag(StoreCallFlagRequest $request, HBL $hbl)
    {
        return $this->HBLRepository->createCallFlag($hbl, $request->all());
    }

    public function getHBLCallFlags(HBL $hbl)
    {
        return response()->json($hbl->callFlags()->with('causer')->get());
    }

    public function getHBLPackageRules(Request $request)
    {
        return $this->HBLRepository->getHBLPackageRules($request->all());
    }

    public function getHBLRules(Request $request)
    {
        return $this->HBLRepository->getHBLRules($request->all());
    }

    public function getCashierReceipt($hbl)
    {
        return $this->HBLRepository->downloadGatePass($hbl);
    }

    public function showDoorToDoorList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('HBL/HBLDoorToDoorList', []);
    }

    public function getDoorToDoorList(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus']);

        return $this->HBLRepository->getDoorToDoorHBL($limit, $page, $order, $dir, $search, $filters);
    }

    public function downloadBaggagePDF(HBL $hbl)
    {
        //        $this->authorize('hbls.download.baggage');

        return $this->HBLRepository->downloadBaggagePDF($hbl);
    }
}
