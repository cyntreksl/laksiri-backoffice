<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoadedContainerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly LoadedContainerRepositoryInterface $loadedContainerRepository,
        private readonly ContainerRepositoryInterface $containerRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('shipment.index');

        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('Loading/LoadedShipmentList', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
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

    public function destroyDraft(Request $request)
    {
        return $this->loadedContainerRepository->deleteDraft($request->all());
    }

    public function exportManifest($container)
    {
        $this->authorize('shipment.download manifest');

        $container = Container::withoutGlobalScope(BranchScope::class)->findOrFail($container);

        return $this->loadedContainerRepository->downloadManifestFile($container);
    }

    public function exportManifestExcel($container)
    {
        $this->authorize('shipment.download manifest');

        $container = Container::withoutGlobalScope(BranchScope::class)->findOrFail($container);

        return $this->loadedContainerRepository->downloadManifestExcel($container->getKey());
    }

    public function verifyDocument(Request $request)
    {
        return $this->loadedContainerRepository->updateVerificationStatus($request->all());
    }

    public function doorToDoorManifest($container)
    {
        $this->authorize('doortodoor.download manifest');

        $container = Container::withoutGlobalScope(BranchScope::class)->findOrFail($container);

        return $this->loadedContainerRepository->downloadDoorToDoorPdf($container);
    }

    public function downloadLoadingPointDoc($container)
    {
        $this->authorize('hbls.download pdf');

        return $this->loadedContainerRepository->downloadUnloadingPointDoc($container);
    }

    public function getLoadedContainer($id)
    {
        return $this->loadedContainerRepository->getLoadedContainer($id);
    }

    public function loadMHBL(Request $request)
    {
        return $this->loadedContainerRepository->loadMHBL($request->all());
    }

    public function tallySheetDownloadPDF($container)
    {
        $this->authorize('shipment.download tally sheet');

        return $this->loadedContainerRepository->tallySheetDownloadPDF($container);
    }

    public function tallySheetDownloadExcel($container)
    {
        $this->authorize('shipment.download tally sheet');

        return $this->loadedContainerRepository->tallySheetDownloadExcel($container);
    }
}
