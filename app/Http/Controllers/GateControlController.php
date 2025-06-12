<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\ContainerRepositoryInterface;
use Inertia\Inertia;

class GateControlController extends Controller
{
    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
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
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerTypes' => ContainerType::cases(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function updateInboundShipmentStatus() {}
}
