<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLById;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\HBL\GetHBLWithTrashedById;
use App\Actions\HBL\HBLCharges\UpdateHBLDepartureCharges;
use App\Actions\HBL\HBLCharges\UpdateHBLDestinationCharges;
use App\Actions\HBL\Payments\CreateHBLPayment;
use App\Enum\CargoType;
use App\Enum\HBLPaymentStatus;
use App\Enum\HBLType;
use App\Http\Requests\StoreCallFlagRequest;
use App\Http\Requests\StoreHBLRequest;
use App\Http\Requests\StoreRemarksRequest;
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
use App\Models\Container;
use App\Models\Remark;
use App\Models\Scopes\BranchScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function getContainerDetailsByReference(string $reference)
    {
        $hbl = HBL::withoutGlobalScope(BranchScope::class)
            ->where('reference', $reference)
            ->orWhere('hbl_number', $reference)
            ->first();

        if (! $hbl) {
            return response()->json(['message' => 'HBL not found'], 404);
        }

        // Find a related container via HBL packages pivot
        $container = Container::withoutGlobalScope(BranchScope::class)
            ->whereHas('hbl_packages', function ($q) use ($hbl) {
                $q->withoutGlobalScope(BranchScope::class)
                    ->where('hbl_id', $hbl->id);
            })
            ->orderByDesc('estimated_time_of_departure')
            ->first();

        if (! $container) {
            return response()->json(null);
        }

        $etd = $container->estimated_time_of_departure;
        $eta = $container->estimated_time_of_arrival;
        $isPast = $etd ? now()->greaterThan($etd) : false;
        $isEtaPast = $eta ? now()->greaterThan($eta) : false;

        return response()->json([
            'loading_started_at' => $container->loading_started_at,
            'estimated_time_of_departure' => $etd,
            'is_etd_past' => $isPast,
            'estimated_time_of_arrival' => $eta,
            'is_eta_past' => $isEtaPast,
            'port_of_discharge' => $container->port_of_discharge,
            'reached_date' => $container->reached_date,
            'arrived_at_primary_warehouse' => $container->arrived_at_primary_warehouse,
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
        $hbl = GetHBLByIdWithPackages::run($hbl_id);
        
        return response()->json([
            'hbl' => array_merge($hbl->toArray(), [
                'finance_status' => $hbl->is_finance_release_approved ? 'Approved' : 'Not Approved',
                'finance_approval_pending' => ! $hbl->is_finance_release_approved && $hbl->system_status > 4.2,
            ]),
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

    public function checkHBLNumberExists(string $hblNumber): JsonResponse
    {
        try {
            $exists = HBL::withoutGlobalScope(BranchScope::class)
                ->where('hbl_number', $hblNumber)
                ->exists();

            return response()->json([
                'exists' => $exists,
                'hbl_number' => $hblNumber
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking HBL number: ' . $e->getMessage());
            return response()->json([
                'exists' => false,
                'error' => 'Failed to check HBL number'
            ], 500);
        }
    }

    public function getHBLDetailsByReference(string $reference)
    {
        $hbl = HBL::withoutGlobalScope(BranchScope::class)
            ->with([
                'pickup' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class);
                },
                'shipper',
                'consignee',
                'packages' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class);
                },
            ])
            ->where('reference', $reference)
            ->orWhere('hbl_number', $reference)
            ->first();

        if (! $hbl) {
            return response()->json(['message' => 'HBL not found'], 404);
        }

        $pickup = $hbl->pickup;

        $payload = [
            'booking_received_date' => $pickup?->created_at ?? $hbl->created_at,
            'booking_assign_to_driver_date' => $pickup?->driver_assigned_at,
            'cargo_received_date' => $hbl->created_at,
            'shipper_name' => $hbl->hbl_name,
            'consignee_name' => $hbl->consignee_name,
            'packages_count' => $hbl->packages?->count() ?? 0,
        ];

        return response()->json($payload);
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

    public function streamCashierReceipt($hbl)
    {
        return $this->HBLRepository->streamCashierInvoice($hbl);
    }

    public function downloadPOSReceipt($hbl)
    {
        return $this->HBLRepository->downloadCashierReceipt($hbl);
    }

    public function streamPOSReceipt($hbl)
    {
        return $this->HBLRepository->streamCashierReceipt($hbl);
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

    public function setPackageDetain(HBLPackage $hbl_package, Request $request)
    {
        $request->validate([
            'detain_type' => 'required|string',
            'detain_reason' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
        ]);

        return $this->HBLRepository->doPackageDetain(
            $hbl_package,
            $request->input('detain_type'),
            $request->input('detain_reason'),
            $request->input('remarks')
        );
    }

    public function unsetPackageDetain(HBLPackage $hbl_package, Request $request)
    {
        $request->validate([
            'lift_reason' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
        ]);

        return $this->HBLRepository->undoPackageDetain(
            $hbl_package,
            $request->input('lift_reason'),
            $request->input('remarks')
        );
    }

    public function setHBLDetain(HBL $hbl, Request $request)
    {
        $request->validate([
            'detain_type' => 'required|string',
            'detain_reason' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
        ]);

        return $this->HBLRepository->doHBLDetain(
            $hbl,
            $request->input('detain_type'),
            $request->input('detain_reason'),
            $request->input('remarks')
        );
    }

    public function unsetHBLDetain(HBL $hbl, Request $request)
    {
        $request->validate([
            'lift_reason' => 'required|string|max:500',
            'remarks' => 'nullable|string|max:1000',
        ]);

        return $this->HBLRepository->undoHBLDetain(
            $hbl,
            $request->input('lift_reason'),
            $request->input('remarks')
        );
    }

    public function getDetainHistory(HBL $hbl): JsonResponse
    {
        try {
            // Get all detain records for this HBL
            $hblDetains = $hbl->detainRecords()
                ->with(['detainedBy', 'liftedBy'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get detain records from all packages of this HBL
            $packageDetains = HBLPackage::where('hbl_id', $hbl->id)
                ->with(['detainRecords' => function ($query) {
                    $query->with(['detainedBy', 'liftedBy'])
                        ->orderBy('created_at', 'desc');
                }])
                ->get()
                ->pluck('detainRecords')
                ->flatten();

            // Get detain records from the shipment (container) if HBL is loaded
            $shipmentDetains = collect();
            if ($hbl->container_id) {
                $shipmentDetains = Container::withoutGlobalScope(BranchScope::class)
                    ->where('id', $hbl->container_id)
                    ->with(['detainRecords' => function ($query) {
                        $query->with(['detainedBy', 'liftedBy'])
                            ->orderBy('created_at', 'desc');
                    }])
                    ->first()
                    ?->detainRecords ?? collect();
            }

            // Merge all detain records and sort by date
            $allDetains = $hblDetains
                ->concat($packageDetains)
                ->concat($shipmentDetains)
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'action' => $record->action,
                        'detain_type' => $record->detain_type,
                        'detain_reason' => $record->detain_reason,
                        'lift_reason' => $record->lift_reason,
                        'remarks' => $record->remarks,
                        'entity_level' => $record->entity_level,
                        'is_rtf' => $record->is_rtf,
                        'created_at' => $record->created_at,
                        'lifted_at' => $record->lifted_at,
                        'detained_by' => $record->detainedBy ? [
                            'id' => $record->detainedBy->id,
                            'name' => $record->detainedBy->name,
                        ] : null,
                        'lifted_by_user' => $record->liftedBy ? [
                            'id' => $record->liftedBy->id,
                            'name' => $record->liftedBy->name,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $allDetains,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching detain history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch detain history',
            ], 500);
        }
    }

    public function hblChargeDetails(Request $request)
    {
        $id = $request->id;
        $hbl = GetHBLById::run($id);

        $hbl->load([
            'departureCharge',
            'destinationCharge',
        ]);

        $chargeDetails = [
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

    /**
     * Get all payments for a given HBL
     */
    public function getHBLPayments($hbl_id)
    {
        $payments = \App\Models\Payment::where('hbl_id', $hbl_id)
            ->orderByDesc('paid_at')
            ->get();

        return response()->json($payments);
    }

    /**
     * Cancel a payment by ID with optional reason
     */
    public function cancelPayment($paymentId, Request $request)
    {
        $payment = \App\Models\Payment::findOrFail($paymentId);
        $payment->is_cancelled = true;
        $payment->cancelled_at = now();
        $payment->cancellation_reason = $request->input('reason');
        $payment->save();

        // Update HBL's paid_amount to exclude cancelled payments
        $hbl = \App\Models\HBL::find($payment->hbl_id);
        if ($hbl) {
            $totalPaid = \App\Models\Payment::where('hbl_id', $hbl->id)
                ->where('is_cancelled', false)
                ->sum('paid_amount');
            $hbl->paid_amount = $totalPaid;
            $hbl->save();
        }

        return response()->json(['success' => true]);
    }

    public function calculatePaymentManual(string $hbl_number)
    {
        // Get HBL using HBL number
        $hbl = HBL::with(['payments', 'departureCharge', 'destinationCharge'])
            ->where('hbl_number', $hbl_number)
            ->first();

        if (! $hbl) {
            return response()->json([
                'success' => false,
                'message' => 'HBL not found',
                'errors' => ['hbl_number' => 'No HBL found with the provided number'],
            ], 404);
        }

        try {
            DB::beginTransaction();

            $messages = [];
            $warnings = [];

            // Only create a payment record if paid_amount exists, is greater than 0,
            // and no existing payment with the same amount exists
            if ($hbl->paid_amount > 0) {
                $existingPayment = $hbl->payments()
                    ->first();

                if (! $existingPayment) {
                    $newPaymentData = [
                        'hbl_id' => $hbl->id,
                        'base_currency_rate_in_lkr' => $hbl->currency_rate,
                        'paid_amount' => $hbl->paid_amount,
                        'total_amount' => $hbl->grand_total,
                        'due_amount' => $hbl->grand_total - $hbl->paid_amount,
                        'payment_method' => 'cash',
                        'paid_by' => $hbl->created_by,
                        'notes' => 'Initial payment',
                    ];
                    CreateHBLPayment::run($newPaymentData);
                    $messages[] = 'Payment record created successfully';
                } else {
                    $warnings[] = 'Payment already exists for this HBL';
                }
            }

            // Prepare payment data
            $paymentData = [
                'freight_charge' => $hbl->freight_charge ?? 0,
                'bill_charge' => $hbl->bill_charge ?? 0,
                'other_charge' => $hbl->other_charge ?? 0,
                'destination_charge' => $hbl->destination_charge ?? 0,
                'package_charges' => $hbl->package_charges ?? 0,
                'discount' => $hbl->discount ?? 0,
                'additional_charge' => $hbl->additional_charge ?? 0,
                'grand_total' => $hbl->grand_total ?? 0,
                'paid_amount' => $hbl->paid_amount ?? 0,
                'is_departure_charges_paid' => $hbl->is_departure_charges_paid ?? false,
                'is_destination_charges_paid' => $hbl->is_destination_charges_paid ?? false,
            ];

            // Update or create departure charges if not exists
            if (! $hbl->departureCharge) {
                UpdateHBLDepartureCharges::run($hbl, $paymentData);
                $messages[] = 'Departure charges created successfully';
            } else {
                $warnings[] = 'Departure charges already exist';
                Log::info("Departure charge already exists for HBL {$hbl->hbl_number}");
            }

            // Update or create destination charges if not exists
            if (! $hbl->destinationCharge) {
                UpdateHBLDestinationCharges::run($hbl, $paymentData);
                $messages[] = 'Destination charges created successfully';
            } else {
                $warnings[] = 'Destination charges already exist';
                Log::info("Destination charge already exists for HBL {$hbl->hbl_number}");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Operation completed successfully',
                'data' => $hbl->fresh(['payments', 'departureCharge', 'destinationCharge']),
                'messages' => $messages,
                'warnings' => $warnings,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment calculation failed for HBL {$hbl_number}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment calculation',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null,
            ], 500);
        }
    }

    public function storeRemark(StoreRemarksRequest $request, Hbl $hbl)
    {
        $remark = new Remark;
        $remark->body = $request->body;
        $remark->user_id = Auth::id();
        $hbl->remarks()->save($remark);
    }

    public function storePackageRemark(StoreRemarksRequest $request, HBLPackage $hbl_package)
    {
        $remark = new Remark;
        $remark->body = $request->body;
        $remark->user_id = Auth::id();
        $hbl_package->remarks()->save($remark);
    }

    public function updateHBLStatus(Request $request, HBL $hbl)
    {
        $request->validate([
            'is_short_load' => 'sometimes|boolean',
            'is_unmanifest' => 'sometimes|boolean',
            'is_overland' => 'sometimes|boolean',
        ]);

        $hbl->update($request->only(['is_short_load', 'is_unmanifest', 'is_overland']));

        return response()->json([
            'success' => true,
            'message' => 'HBL status updated successfully',
            'hbl' => $hbl->fresh()
        ]);
    }

    public function showStatusDefault()
    {
        $this->authorize('hbls.index');

        return Inertia::render('HBL/HBLStatusDefault', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }
}
