<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Interfaces\ContainerRepositoryInterface;
use App\Models\Container;
use App\Models\CustomerQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GateControlController extends Controller
{
    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
        private readonly ExaminationRepositoryInterface $examinationRepository,
    ) {}

    public function listInboundShipments()
    {
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('GateControl/InboundShipments', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function getAfterDispatchShipmentsList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->getAfterDispatchShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function updateInboundShipmentStatus(Container $container)
    {
        $this->containerRepository->updateInboundShipmentStatus($container);
    }

    public function listOutboundShipments()
    {
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('GateControl/OutboundShipments', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function getAfterInboundShipmentsList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->getAfterInboundShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function updateOutboundShipmentStatus(Container $container)
    {
        $this->containerRepository->updateOutboundShipmentStatus($container);
    }

    public function listOutboundGatePasses()
    {
        return Inertia::render('GateControl/OutboundTokens');
    }

    public function markAsDeparted(CustomerQueue $customerQueue)
    {
        try {
            $this->examinationRepository->markAsDeparted($customerQueue);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as depart: '.$e->getMessage());
        }
    }
}
