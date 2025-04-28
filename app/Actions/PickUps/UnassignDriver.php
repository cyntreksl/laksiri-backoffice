<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class UnassignDriver
{
    use AsAction;

    public function handle(PickUp $pickup)
    {
        $pickup->update([
            'driver_id' => null,
            'driver_assigned_at' => null,
            'system_status' => PickUp::SYSTEM_STATUS_PICKUP_CREATED,
        ]);

        $pickup->addStatus('Driver Unassigned');
    }
}
