<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetBranches;
use App\Actions\Branch\GetDestinationBranches;
use App\Actions\Container\GenerateContainerReferenceNumber;
use App\Actions\Container\GetContainerWithoutGlobalScopesById;
use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use App\Http\Requests\StoreContainerRequest;
use App\Http\Requests\StoreRemarksRequest;
use App\Http\Requests\StoreUnloadingIssue;
use App\Http\Requests\UpdateContainerRequest;
use App\Interfaces\AirLineRepositoryInterface;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\HBL;
use App\Models\Remark;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContainerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly MHBLRepositoryInterface $MHBLRepository,
        private readonly AirLineRepositoryInterface $airLineRepository,
    ) {}

    public function index()
    {
        $this->authorize('container.index');

        return Inertia::render('Container/ContainerList', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status']);

        return $this->containerRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function allShipments()
    {
        $this->authorize('all.shipments.index');

        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        return Inertia::render('Container/AllShipmentList', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
            'containerStatus' => ContainerStatus::cases(),
        ]);
    }

    public function allShipmentsList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status']);

        return $this->containerRepository->getAllShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function ArrivedList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status']);

        return $this->containerRepository->getAfterDispatchShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function create()
    {
        $this->authorize('container.create');

        $containerTypes = ContainerType::getDropdownOptions();
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();
        $cargoTypes = CargoType::getCargoTypeOptions();
        $reference = GenerateContainerReferenceNumber::run();

        return Inertia::render('Container/ContainerCreate', [
            'referenceNum' => $reference,
            'containerTypes' => $containerTypes,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
            'cargoTypes' => $cargoTypes,
            'warehouses' => GetDestinationBranches::run()->reject(fn ($warehouse) => $warehouse->name === 'Other'),
            'airLines' => $this->airLineRepository->getAirLines(),
        ]);
    }

    public function store(StoreContainerRequest $request)
    {
        try {
            $this->containerRepository->store($request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(Container $container)
    {
        $containerTypes = ContainerType::getDropdownOptions();
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();
        $cargoTypes = CargoType::getCargoTypeOptions();

        return Inertia::render('Container/ContainerEdit', [
            'container' => $container,
            'containerTypes' => $containerTypes,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
            'cargoTypes' => $cargoTypes,
            'warehouses' => GetDestinationBranches::run()->reject(fn ($warehouse) => $warehouse->name === 'Other'),
            'airLines' => $this->airLineRepository->getAirLines(),
        ]);
    }

    public function update(Container $container, UpdateContainerRequest $request)
    {
        return $this->containerRepository->update($request->all(), $container);
    }

    public function showLoadingPoint(Request $request, Container $container)
    {
        $this->authorize('container.load to container');
        if (Auth::user()->hasRole('boned area')) {
            return Inertia::render('Loading/DestinationLoadingPoint', [
                'container' => $container,
                'loadedHBLs' => $this->HBLRepository->getLoadedHBLsByCargoType($container, $request->cargoType),
                'cargoTypes' => CargoType::getCargoTypeOptions(),
                'hblTypes' => HBLType::getHBLTypeOptions(),
                'warehouses' => WarehouseType::getWarehouseOptions(),
            ]);
        } else {
            return Inertia::render('Loading/LoadingPoint', [
                'container' => $container,
                'loadedHBLs' => $this->HBLRepository->getLoadedHBLsByCargoType($container, $request->cargoType),
                'cargoTypes' => CargoType::getCargoTypeOptions(),
                'hblTypes' => HBLType::getHBLTypeOptions(),
                'warehouses' => WarehouseType::getWarehouseOptions(),
            ]);
        }

    }

    public function showUnloadingPoint($container_id)
    {
        $this->authorize('arrivals.unload');

        $container = GetContainerWithoutGlobalScopesById::run($container_id);
        $packagesWithMhbl = [];
        $packagesWithoutMhbl = [];

        foreach ($container->hbl_packages as $package) {
            if (! empty($package->hbl['mhbl'])) {
                $packagesWithMhbl[] = $package;
            } else {
                $packagesWithoutMhbl[] = $package;
            }
        }

        return Inertia::render('Arrival/UnloadingPoint', [
            'container' => $container,
            'cargoTypes' => CargoType::getCargoTypeOptions(),
            'hblTypes' => HBLType::getHBLTypeOptions(),
            'warehouses' => WarehouseType::getWarehouseOptions(),
            'packagesWithMhbl' => $packagesWithMhbl,
            'packagesWithoutMhbl' => $packagesWithoutMhbl,
        ]);
    }

    public function getUnloadedHBLs(Request $request)
    {
        return $this->HBLRepository->getUnloadedHBLsByCargoType($request->all());
    }

    public function unloadHBLFromContainer(Request $request, Container $container)
    {
        return $this->containerRepository->unloadHBLFromContainer($request->all(), $container);
    }

    public function getHBLWithUnloadedPackages(Request $request)
    {
        return $this->HBLRepository->getHBLWithUnloadedPackagesByReference($request->reference, $request->cargo_type);
    }

    public function batchDownloadPDF(Container $container)
    {
        return $this->containerRepository->batchHBLDownload($container);
    }

    public function batchMHBLDownloadPDF(Container $container)
    {
        $this->authorize('hbls.download pdf');

        return $this->containerRepository->batchMHBLDownload($container);
    }

    public function deleteLoading(Container $container)
    {
        return $this->containerRepository->deleteLoading($container);
    }

    public function showShipmentArrivals()
    {
        $this->authorize('arrivals.index');

        return Inertia::render('Arrival/ShipmentsArrivalsList', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => ContainerStatus::cases(),
            'branches' => GetBranches::run(),
            'seaContainerOptions' => ContainerType::getSeaCargoOptions(),
            'airContainerOptions' => ContainerType::getAirCargoOptions(),
        ]);
    }

    public function unloadContainer(Request $request)
    {
        $this->containerRepository->unloadContainer($request->all());
    }

    public function reloadContainer(Request $request)
    {
        $this->containerRepository->reloadContainer($request->all());
    }

    public function unloadHBLGroup(Request $request)
    {
        $this->containerRepository->unloadHBLGroup($request->all());
    }

    public function unloadMHBLGroup(Request $request)
    {
        $this->containerRepository->unloadMHBLGroup($request->all());
    }

    public function storeUnloadingIssue(StoreUnloadingIssue $request)
    {
        $this->containerRepository->createUnloadingIssue($request->all());
    }

    public function markAsReachedContainer($container_id)
    {
        $this->authorize('arrivals.mark as reached');

        $this->containerRepository->markAsReached($container_id);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status']);

        return $this->containerRepository->export($filters);
    }

    public function exportLoadedShipments(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->exportLoadedShipments($filters);
    }

    public function exportShipmentArrivals(Request $request)
    {
        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->exportShipmentArrivals($filters);
    }

    public function getContainerDocuments(Container $container)
    {
        return response()->json($container->containerDocuments);
    }

    public function uploadDocument(Request $request)
    {
        $this->authorize('container.upload documents');

        return $this->containerRepository->uploadDocument($request->all());
    }

    public function destroyContainerDocument(ContainerDocument $containerDocument)
    {
        $this->authorize('container.delete documents');

        $this->containerRepository->deleteDocument($containerDocument);
    }

    public function getContainerByHBL(HBL $hbl)
    {
        return $this->containerRepository->getContainerByHBL($hbl);
    }

    public function downloadDocument(ContainerDocument $container_document)
    {
        $this->authorize('container.download documents');

        return $this->containerRepository->downloadDocument($container_document);
    }

    public function unloadMHBLFromContainer(Request $request, Container $container)
    {
        return $this->containerRepository->unloadMHBLFromContainer($request->all(), $container);
    }

    public function getDestinationUnloadedHBLs(Request $request)
    {
        return $this->HBLRepository->getDestinationUnloadedHBLsByCargoType($request->all());
    }

    public function getUnloadedMHBLHBL(Request $request)
    {
        return $this->MHBLRepository->getUnloadedMHBLHBL($request->all()['reference']);
    }

    public function getContainerByReference(Request $request, string $reference)
    {
        return $this->containerRepository->getContainerByReference($reference, $request->vesselScheduleId);
    }

    public function setRTF(Container $container)
    {
        return $this->containerRepository->doRTF($container);
    }

    public function unsetRTF(Container $container)
    {
        return $this->containerRepository->undoRTF($container);
    }

    public function setContainerDetain(Request $request, Container $container)
    {
        $validated = $request->validate([
            'detain_type' => 'required|string',
            'detain_reason' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            $this->containerRepository->doDetain(
                $container,
                $validated['detain_type'],
                $validated['detain_reason'],
                $validated['remarks'] ?? null
            );

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function unsetContainerDetain(Request $request, Container $container)
    {
        $validated = $request->validate([
            'lift_reason' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            $this->containerRepository->undoDetain(
                $container,
                $validated['lift_reason'],
                $validated['remarks'] ?? null
            );

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function getDetainHistory($containerId): \Illuminate\Http\JsonResponse
    {
        try {
            // Get container without global scope to avoid branch filtering issues
            $container = Container::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->with('hbl_packages')
                ->findOrFail($containerId);

            // Get all detain records for this container
            $containerRecords = $container->detainRecords()
                ->with(['detainedBy', 'liftedBy'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'entity_type' => 'Shipment',
                        'entity_reference' => $record->rtfable->reference ?? 'N/A',
                        'action' => $record->action,
                        'detain_type' => $record->detain_type,
                        'detain_reason' => $record->detain_reason,
                        'lift_reason' => $record->lift_reason,
                        'remarks' => $record->remarks,
                        'is_rtf' => $record->is_rtf,
                        'entity_level' => $record->entity_level,
                        'created_by' => $record->detainedBy ? [
                            'id' => $record->detainedBy->id,
                            'name' => $record->detainedBy->name,
                        ] : null,
                        'lifted_by' => $record->liftedBy ? [
                            'id' => $record->liftedBy->id,
                            'name' => $record->liftedBy->name,
                        ] : null,
                        'created_at' => $record->created_at,
                        'lifted_at' => $record->lifted_at,
                    ];
                });

            // Get unique HBL IDs from packages
            $hblIds = $container->hbl_packages->pluck('hbl_id')->unique()->filter();

            // Get all HBL records from packages in this container
            $hblRecords = collect([]);
            if ($hblIds->isNotEmpty()) {
                $hblRecords = \App\Models\DetainRecord::whereIn('rtfable_id', $hblIds)
                    ->where('rtfable_type', 'App\\Models\\HBL')
                    ->with(['detainedBy', 'liftedBy', 'rtfable'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($record) {
                        return [
                            'id' => $record->id,
                            'entity_type' => 'HBL',
                            'entity_reference' => $record->rtfable->reference ?? 'N/A',
                            'action' => $record->action,
                            'detain_type' => $record->detain_type,
                            'detain_reason' => $record->detain_reason,
                            'lift_reason' => $record->lift_reason,
                            'remarks' => $record->remarks,
                            'is_rtf' => $record->is_rtf,
                            'entity_level' => $record->entity_level,
                            'created_by' => $record->detainedBy ? [
                                'id' => $record->detainedBy->id,
                                'name' => $record->detainedBy->name,
                            ] : null,
                            'lifted_by' => $record->liftedBy ? [
                                'id' => $record->liftedBy->id,
                                'name' => $record->liftedBy->name,
                            ] : null,
                            'created_at' => $record->created_at,
                            'lifted_at' => $record->lifted_at,
                        ];
                    });
            }

            // Get package IDs
            $packageIds = $container->hbl_packages->pluck('id')->filter();

            // Get all package records from this container
            $packageRecords = collect([]);
            if ($packageIds->isNotEmpty()) {
                $packageRecords = \App\Models\DetainRecord::whereIn('rtfable_id', $packageIds)
                    ->where('rtfable_type', 'App\\Models\\HBLPackage')
                    ->with(['detainedBy', 'liftedBy', 'rtfable'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($record) {
                        return [
                            'id' => $record->id,
                            'entity_type' => 'Package',
                            'entity_reference' => $record->rtfable->package_number ?? 'N/A',
                            'action' => $record->action,
                            'detain_type' => $record->detain_type,
                            'detain_reason' => $record->detain_reason,
                            'lift_reason' => $record->lift_reason,
                            'remarks' => $record->remarks,
                            'is_rtf' => $record->is_rtf,
                            'entity_level' => $record->entity_level,
                            'created_by' => $record->detainedBy ? [
                                'id' => $record->detainedBy->id,
                                'name' => $record->detainedBy->name,
                            ] : null,
                            'lifted_by' => $record->liftedBy ? [
                                'id' => $record->liftedBy->id,
                                'name' => $record->liftedBy->name,
                            ] : null,
                            'created_at' => $record->created_at,
                            'lifted_at' => $record->lifted_at,
                        ];
                    });
            }

            // Merge all records and sort by created_at
            $allRecords = $containerRecords
                ->concat($hblRecords)
                ->concat($packageRecords)
                ->sortByDesc('created_at')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $allRecords,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching detain history: ' . $e->getMessage(), [
                'container_id' => $containerId ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch detain history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeRemark(StoreRemarksRequest $request, Container $container)
    {
        $remark = new Remark;
        $remark->body = $request->body;
        $remark->user_id = Auth::id();
        $container->remarks()->save($remark);
    }
}
