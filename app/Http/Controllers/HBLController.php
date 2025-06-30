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
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\HBLDocument;
use App\Models\HBLPackage;
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

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);

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
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function cancelledList(Request $request): JsonResponse
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);

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
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);

        return $this->HBLRepository->export($filters);
    }

    public function exportCancelled(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);

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
            'users' => $this->userRepository->getUsers(['customer']),
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
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
        ]);
    }

    public function getDraftList(Request $request): JsonResponse
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'paymentStatus']);

        return $this->HBLRepository->getDraftList($limit, $page, $order, $dir, $search, $filters);
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
        return $this->HBLRepository->downloadCashierInvoice($hbl);
    }

    public function showDoorToDoorList()
    {
        $this->authorize('hbls.show door to door list');

        return Inertia::render('HBL/HBLDoorToDoorList', [
            'warehouses' => GetDestinationBranches::run(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'users' => $this->userRepository->getUsers(['customer']),
        ]);
    }

    public function getDoorToDoorList(Request $request): JsonResponse
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus', 'warehouse']);

        return $this->HBLRepository->getDoorToDoorHBL($limit, $page, $order, $dir, $search, $filters);
    }

    public function downloadBaggagePDF(HBL $hbl)
    {
        //        $this->authorize('hbls.download.baggage');

        return $this->HBLRepository->downloadBaggagePDF($hbl);
    }

    public function getHBLTotalSummary(HBL $hbl)
    {
        return $this->HBLRepository->getHBLTotalSummary($hbl);
    }

    public function getHBLsPackages(Request $request)
    {
        return $this->HBLRepository->getHBLsPackages($request->all());
    }

    public function getHBLDestinationTotalSummary(HBL $hbl)
    {
        return $this->HBLRepository->getHBLDestinationTotalSummary($hbl);
    }

    public function downloadGatePass($hbl, CustomerQueue $customerQueue)
    {
        return $this->HBLRepository->downloadGatePass($hbl, $customerQueue);
    }

    public function setRTF(HBL $hbl)
    {
        return $this->HBLRepository->doRTF($hbl);
    }

    public function unsetRTF(HBL $hbl)
    {
        return $this->HBLRepository->undoRTF($hbl);
    }

    public function setPackageRTF(HBLPackage $hbl_package)
    {
        return $this->HBLRepository->doPackageRTF($hbl_package);
    }

    public function unsetPackageRTF(HBLPackage $hbl_package)
    {
        return $this->HBLRepository->undoPackageRTF($hbl_package);
    }

    public function hblChargeDetails(Request $request)
    {
        $id = $request->id;
        $hbl = GetHBLById::run($id);

        $hbl->load([
            'departureCharge',
            'destinationCharge',
        ]);

        $chargeDetails =[
            'base_currency_code' => $hbl->departureCharge->base_currency_code ?? null,
            'base_currency_rate_in_lkr' => $hbl->departureCharge->base_currency_rate_in_lkr ?? null,
            'is_branch_prepaid' => $hbl->departureCharge->is_branch_prepaid ?? null,
            'freight_charge' => $hbl->departureCharge->freight_charge ?? null,
            'bill_charge' => $hbl->departureCharge->bill_charge ?? null,
            'package_charge' => $hbl->departureCharge->package_charge ?? null,
            'discount' => $hbl->departureCharge->discount ?? null,
            'additional_charges' => $hbl->departureCharge->additional_charges ?? null,
            'departure_grand_total' => $hbl->departureCharge->departure_grand_total ?? null,

            'destination_handling_charge' => $hbl->destinationCharge->destination_handling_charge ?? null,
            'destination_slpa_charge' => $hbl->destinationCharge->destination_slpa_charge ?? null,
            'destination_bond_charge' => $hbl->destinationCharge->destination_bond_charge ?? null,
            'destination_1_total' => $hbl->destinationCharge->destination_1_total ?? null,
            'destination_1_tax' => $hbl->destinationCharge->destination_1_tax ?? null,
            'destination_1_total_with_tax' => $hbl->destinationCharge->destination_1_total_with_tax ?? null,

            'destination_do_charge' => $hbl->destinationCharge->destination_do_charge ?? null,
            'destination_demurrage_charge' => $hbl->destinationCharge->destination_demurrage_charge ?? null,
            'destination_stamp_charge' => $hbl->destinationCharge->destination_stamp_charge ?? null,
            'destination_other_charge' => $hbl->destinationCharge->destination_other_charge ?? null,
            'destination_2_total' => $hbl->destinationCharge->destination_2_total ?? null,
            'destination_2_tax' => $hbl->destinationCharge->destination_2_tax ?? null,
            'destination_2_total_with_tax' => $hbl->destinationCharge->destination_2_total_with_tax ?? null,
            'stop_demurrage_at' => $hbl->destinationCharge->stop_demurrage_at ?? null,
        ];

        return response()->json($chargeDetails);


    }
}
