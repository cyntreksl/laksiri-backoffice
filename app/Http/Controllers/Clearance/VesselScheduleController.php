<?php

namespace App\Http\Controllers\Clearance;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class VesselScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Clearance/VesselSchedule/VesselScheduleList');
    }
}
