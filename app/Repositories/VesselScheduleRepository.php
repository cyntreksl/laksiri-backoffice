<?php

namespace App\Repositories;

use App\Actions\VesselSchedule\GetRecentVesselSchedule;
use App\Interfaces\VesselScheduleRepositoryInterface;

class VesselScheduleRepository implements VesselScheduleRepositoryInterface
{
    public function getRecentVesselSchedule()
    {
        return GetRecentVesselSchedule::run();
    }
}
