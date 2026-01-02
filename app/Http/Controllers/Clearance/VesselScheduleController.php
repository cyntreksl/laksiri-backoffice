<?php

namespace App\Http\Controllers\Clearance;

use App\Actions\Branch\GetDestinationBranches;
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
        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList');
    }

    public function getVesselSchedulesData(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $fromDate = $request->get('fromDate');
        $toDate = $request->get('toDate');

        $query = VesselSchedule::with([
            'clearanceContainers' => function ($query) {
                $query->select('containers.id', 'reference', 'container_number');
            }
        ]);

        // Apply date filters
        if ($fromDate && $toDate) {
            $query->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                    ->orWhereBetween('end_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('start_date', '<=', $fromDate)
                            ->where('end_date', '>=', $toDate);
                    });
            });
        }

        $vesselSchedules = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $vesselSchedules->items(),
            'meta' => [
                'current_page' => $vesselSchedules->currentPage(),
                'last_page' => $vesselSchedules->lastPage(),
                'per_page' => $vesselSchedules->perPage(),
                'total' => $vesselSchedules->total(),
                'from' => $vesselSchedules->firstItem(),
                'to' => $vesselSchedules->lastItem(),
            ],
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
            'warehouses' => GetDestinationBranches::run()->reject(fn ($warehouse) => $warehouse->name === 'Other'),
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

    public function downloadShipmentReleasePDF(Container $container)
    {
        return $this->vesselScheduleRepository->downloadShipmentReleasePDF($container);
    }
}
