<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use App\Models\LoadedContainer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoadedContainerController extends Controller
{
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
        return Inertia::render('Loading/LoadedShipmentList', [
            'cargoTypes' => CargoType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => ContainerStatus::cases(),
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
    public function show(LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoadedContainer $loadedContainer)
    {
        //
    }

    public function destroyDraft(Request $request)
    {
        return $this->loadedContainerRepository->deleteDraft($request->all());
    }

    public function exportManifest(Container $container)
    {
        return $this->loadedContainerRepository->downloadManifestFile($container);
    }
}
