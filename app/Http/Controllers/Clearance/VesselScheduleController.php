<?php

namespace App\Http\Controllers\Clearance;

use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Http\Controllers\Controller;
use App\Interfaces\VesselScheduleRepositoryInterface;
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
        $vesselSchedule = $this->vesselScheduleRepository->getRecentVesselSchedule();
        if ($vesselSchedule) {
            $vesselSchedule->load(['containers.branch', 'containers.warehouse', 'containers.hbl_packages']);
        }

        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList', [
            'vesselSchedules' => $vesselSchedule,
            'containers' => $vesselSchedule->containers ?? [],
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, Request $request)
    {
        return $this->vesselScheduleRepository->addVesselToSchedule($vesselSchedule->first(), $request['reference']);
    }

    public function removeVesselFromSchedule(VesselSchedule $vesselSchedule, Request $request)
    {
        $this->vesselScheduleRepository->removeVesselFromSchedule($vesselSchedule->first(), $request['containerID']);
    }
}
