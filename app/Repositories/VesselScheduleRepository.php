<?php

namespace App\Repositories;

use App\Actions\VesselSchedule\AddContainerToVesselSchedule;
use App\Actions\VesselSchedule\DownloadVesselSchedule;
use App\Actions\VesselSchedule\GetRecentVesselSchedule;
use App\Actions\VesselSchedule\RemoveContainerFromVesselSchedule;
use App\Interfaces\VesselScheduleRepositoryInterface;
use App\Models\Container;
use App\Models\VesselSchedule;

class VesselScheduleRepository implements VesselScheduleRepositoryInterface
{
    public function getRecentVesselSchedule()
    {
        return GetRecentVesselSchedule::run();
    }

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, string $vesselReference)
    {
        $container = Container::where('reference', $vesselReference)->first();
        $is_scheduled_container = $vesselSchedule->scheduleContainers()
            ->where('container_id', $container->id)
            ->first();
        if (! $container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container not found or invalid reference number.'],
                ],
            ], 422);
        } elseif ($container->is_reached) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already reached to destination.'],
                ],
            ], 422);
        } elseif ($is_scheduled_container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already scheduled.'],
                ],
            ], 422);
        } else {
            return AddContainerToVesselSchedule::run($vesselSchedule->first(), $container);
        }
    }

    public function removeVesselFromSchedule(VesselSchedule $vesselSchedule, int $containerId)
    {
        return RemoveContainerFromVesselSchedule::run($vesselSchedule, $containerId);
    }

    public function downloadVesselSchedulePDF(VesselSchedule $vesselSchedule)
    {
        return DownloadVesselSchedule::run($vesselSchedule);
    }
}
