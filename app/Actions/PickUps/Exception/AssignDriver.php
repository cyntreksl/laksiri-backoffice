<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignDriver
{
    use AsAction;

    public function handle(PickupException $pickupException, string|int $driver_id)
    {
        $pickupException->update([
            'driver_id' => $driver_id,
            'driver_assigned_at' => Carbon::now(),
            'system_status' => 2,
        ]);
    }
}
