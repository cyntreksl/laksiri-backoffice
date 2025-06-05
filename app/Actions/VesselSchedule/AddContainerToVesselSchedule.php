<?php

namespace App\Actions\VesselSchedule;

use App\Models\Container;
use App\Models\VesselSchedule;
use App\Models\VesselScheduleContainer;
use Lorisleiva\Actions\Concerns\AsAction;

class AddContainerToVesselSchedule
{
    use AsAction;

    public function handle(VesselSchedule $vesselSchedule, Container $container)
    {
        // Check if the container is already associated with any vessel schedule
        // If it exists, remove it first (effectively updating it or ensuring uniqueness)
        $existingContainer = VesselScheduleContainer::where('container_id', $container->id)
            ->first();

        if ($existingContainer) {
            $existingContainer->delete();
        }

        $vesselSchedule->scheduleContainers()->create([
            'container_id' => $container->id,
        ]);

        $vesselSchedule->update([
            'status' => 'USER_GENERATED',
        ]);
    }
}
