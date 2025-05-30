<?php

namespace App\Http\Controllers\Clearance;

use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClearanceContainerRequest;
use App\Interfaces\VesselScheduleRepositoryInterface;
use App\Models\Container;
use App\Models\VesselSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VesselScheduleController extends Controller
{
    public function __construct(
        private readonly VesselScheduleRepositoryInterface $vesselScheduleRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList', [
            'vesselSchedules' => $this->vesselScheduleRepository->getAllVesselSchedules(),
        ]);
    }

    public function show(VesselSchedule $vesselSchedule)
    {
        $vesselSchedule->load(['clearanceContainers' => function ($query) {
            $query->with(['branch', 'warehouse', 'hbl_packages']);
        }]);

        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, [ContainerStatus::DRAFT->value, ContainerStatus::REQUESTED->value]);
        }));

        return Inertia::render('Clearance/VesselSchedule/ShowVesselSchedule', [
            'vesselSchedule' => $vesselSchedule,
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function addContainerToSchedule(Request $request, VesselSchedule $vesselSchedule)
    {
        return $this->vesselScheduleRepository->addContainerToSchedule($vesselSchedule, $request['containerId']);
    }

    public function removeContainerFromSchedule(Request $request, VesselSchedule $vesselSchedule)
    {
        $this->vesselScheduleRepository->removeContainerFromSchedule($vesselSchedule, $request['containerID']);
    }

    public function downloadVesselSchedulePDF(VesselSchedule $vesselSchedule)
    {
        return $this->vesselScheduleRepository->downloadVesselSchedulePDF($vesselSchedule);
    }

    public function updateContainer(Container $container, UpdateClearanceContainerRequest $request)
    {
        return $this->vesselScheduleRepository->updateContainer($container, $request->all());
    }
}
