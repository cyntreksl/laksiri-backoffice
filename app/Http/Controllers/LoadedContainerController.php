<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoadedContainerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly LoadedContainerRepositoryInterface $loadedContainerRepository,
        private readonly ContainerRepositoryInterface $containerRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('shipment.index');

        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        });

        return Inertia::render('Loading/LoadedShipmentList', [
            'cargoTypes' => CargoType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->loadedContainerRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->loadedContainerRepository->store($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($loadedContainer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($loadedContainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $loadedContainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($loadedContainer)
    {
        //
    }

    public function destroyDraft(Request $request)
    {
        return $this->loadedContainerRepository->deleteDraft($request->all());
    }

    public function exportManifest(Container $container)
    {
        $this->authorize('shipment.download manifest');

        return $this->loadedContainerRepository->downloadManifestFile($container);
    }
}
