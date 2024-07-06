<?php

namespace App\Http\Controllers;

use App\Actions\Branch\GetBranches;
use App\Actions\Container\GenerateContainerReferenceNumber;
use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use App\Http\Requests\StoreContainerRequest;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Models\Container;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContainerController extends Controller
{
    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {
    }

    public function index()
    {
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

    public function update(Request $request, Container $container)
    {
        return $this->containerRepository->update($request->all(), $container);
    }

    public function showLoadingPoint(Request $request, Container $container)
    {
        return Inertia::render('Loading/LoadingPoint', [
            'container' => $container,
            'loadedHBLs' => $this->HBLRepository->getLoadedHBLsByCargoType($container, $request->cargoType),
            'cargoTypes' => CargoType::getCargoTypeOptions(),
            'hblTypes' => HBLType::getHBLTypeOptions(),
            'warehouses' => WarehouseType::getWarehouseOptions(),
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
        return Inertia::render('Arrival/ShipmentsArrivalsList', [
            'cargoTypes' => CargoType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => ContainerStatus::cases(),
            'branches' => GetBranches::run(),
        ]);
    }
}
