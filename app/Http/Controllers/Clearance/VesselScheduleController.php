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

        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList');
    }
}
