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
use App\Http\Requests\StoreUnloadingIssue;
use App\Http\Requests\UpdateContainerRequest;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\HBL;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContainerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {
    }

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
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status']);

        return $this->containerRepository->dataset($limit, $page, $order, $dir, $search, $filters);
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
        ]);
    }

    public function update(Container $container, UpdateContainerRequest $request)
    {
        return $this->containerRepository->update($request->all(), $container);
    }

    public function showLoadingPoint(Request $request, Container $container)
    {
        $this->authorize('container.load to container');

        return Inertia::render('Loading/LoadingPoint', [
            'container' => $container,
            'loadedHBLs' => $this->HBLRepository->getLoadedHBLsByCargoType($container, $request->cargoType),
            'cargoTypes' => CargoType::getCargoTypeOptions(),
            'hblTypes' => HBLType::getHBLTypeOptions(),
            'warehouses' => WarehouseType::getWarehouseOptions(),
        ]);
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

    public function deleteLoading(Container $container)
    {
        return $this->containerRepository->deleteLoading($container);
    }

    public function showShipmentArrivals()
    {
        $this->authorize('arrivals.index');

        return Inertia::render('Arrival/ShipmentsArrivalsList', [
            'cargoTypes' => CargoType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
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
}
