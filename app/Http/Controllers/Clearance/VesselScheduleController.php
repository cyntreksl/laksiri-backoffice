<?php

namespace App\Http\Controllers\Clearance;

use App\Http\Controllers\Controller;
use App\Interfaces\VesselScheduleRepositoryInterface;
use Inertia\Inertia;

class VesselScheduleController extends Controller
{
    public function __construct(
        private readonly VesselScheduleRepositoryInterface $vesselScheduleRepository,
    ) {}

    public function index()
    {
        $vesselSchedule = $this->vesselScheduleRepository->getRecentVesselSchedule();
        $vesselSchedule->load(['containers.warehouse', 'containers.hbl_packages']);

        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList', [
            'vesselSchedules' => $vesselSchedule,
            'containers' => $vesselSchedule->containers,
        ]);
    }
}
