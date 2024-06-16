<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerType;
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

        return Inertia::render('Container/ContainerCreate', [
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

    public function showLoadingPoint(Request $request, Container $container)
    {
        return Inertia::render('Loading/LoadingPoint', [
            'container' => $container,
            'unloadedHBLs' => $this->HBLRepository->getUnloadedHBLsByCargoType($request->cargoType),
            'loadedHBLs' => $this->HBLRepository->getLoadedHBLsByCargoType($container, $request->cargoType),
        ]);
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
}
