<?php

namespace App\Interfaces;

use App\Models\VesselSchedule;

interface VesselScheduleRepositoryInterface
{
    public function getRecentVesselSchedule();

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, string $vesselReference);
}
