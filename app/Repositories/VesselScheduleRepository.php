<?php

namespace App\Repositories;

use App\Actions\VesselSchedule\AddContainerToVesselSchedule;
use App\Actions\VesselSchedule\GetRecentVesselSchedule;
use App\Interfaces\VesselScheduleRepositoryInterface;
use App\Models\Container;
use App\Models\VesselSchedule;
use App\Models\VesselScheduleContainer;

class VesselScheduleRepository implements VesselScheduleRepositoryInterface
{
    public function getRecentVesselSchedule()
    {
        return GetRecentVesselSchedule::run();
    }

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, String $vesselReference){
        $container = Container::where('reference', $vesselReference)->first();
        $is_scheduled_container = $vesselSchedule->first()->scheduleContainers()
            ->where('container_id', $container->id)
            ->first();
        if (! $container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container not found or invalid reference number.'],
                ],
            ], 422);
        } else if ($container->is_reached) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already reached to destination.'],
                ],
            ], 422);
        }else if($is_scheduled_container){
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already scheduled.'],
                ],
            ], 422);
        }else{
            return AddContainerToVesselSchedule::run($vesselSchedule->first(), $container);
        }
    }
}
