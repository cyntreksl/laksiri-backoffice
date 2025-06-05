<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\VesselSchedule;

interface VesselScheduleRepositoryInterface
{
    public function getAllVesselSchedules();

    public function getRecentVesselSchedule();

    public function addContainerToSchedule(VesselSchedule $vesselSchedule, int $containerId);

    public function removeContainerFromSchedule(VesselSchedule $vesselSchedule, int $containerId);

    public function downloadVesselSchedulePDF(VesselSchedule $vesselSchedule);

    public function updateContainer(Container $container, array $data);

    public function downloadShipmentReleasePDF(Container $container);
}
