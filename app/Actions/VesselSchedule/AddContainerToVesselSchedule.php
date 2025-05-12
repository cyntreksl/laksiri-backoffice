<?php

namespace App\Actions\VesselSchedule;

use App\Models\Container;
use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class AddContainerToVesselSchedule
{
    use AsAction;

    public function handle(VesselSchedule $vesselSchedule, Container $container)
    {
        $vesselSchedule->scheduleContainers()->create([
            'container_id' => $container['id'],
        ]);
    }
}
