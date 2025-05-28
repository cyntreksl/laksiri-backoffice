<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveContainerFromVesselSchedule
{
    use AsAction;

    public function handle(VesselSchedule $vesselSchedule, int $containerId)
    {
        return $vesselSchedule->scheduleContainers()
            ->where('container_id', $containerId)
            ->delete();
    }
}
