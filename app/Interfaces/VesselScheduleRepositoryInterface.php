<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\VesselSchedule;

interface VesselScheduleRepositoryInterface
{
    public function getRecentVesselSchedule();

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, string $vesselReference);

    public function removeVesselFromSchedule(VesselSchedule $vesselSchedule, int $containerId);

    public function downloadVesselSchedulePDF(VesselSchedule $vesselSchedule);

    public function updateContainer(Container $container, array $data);
}
