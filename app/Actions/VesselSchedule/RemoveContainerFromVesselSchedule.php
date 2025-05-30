<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveContainerFromVesselSchedule
{
    use AsAction;

    public function handle(VesselSchedule $vesselSchedule, int $containerId)
    {
        $vesselSchedule->update([
            'status' => 'USER_GENERATED',
        ]);

        return $vesselSchedule->scheduleContainers()
            ->where('container_id', $containerId)
            ->delete();
    }
}
