<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignDriver
{
    use AsAction;

    public function handle(PickUp $pickup, string|int $driver_id)
    {
        $pickup->update([
            'driver_id' => $driver_id,
            'driver_assigned_at' => Carbon::now(),
            'system_status' => 2,
        ]);
    }
}
