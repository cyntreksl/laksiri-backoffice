<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignDriver
{
    use AsAction;

    public function handle(array $data, PickUp $pickUp): PickUp
    {
        $pickUp->update([
            'driver_id' => $data['driver_id'],
            'driver_assigned_at' => Carbon::now(),
        ]);

        return $pickUp;
    }
}
