<?php

namespace App\Actions\PickUps\Exception;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\PickUp;
use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePickUpException
{
    use AsAction;

    public function handle(array $data, PickUp $pickup): PickupException
    {
        return $pickup->pickupException()->create([
            'driver_id' => auth()->id() ?? $pickup->driver_id,
            'zone_id' => $pickup->zone_id,
            'branch_id' => GetUserCurrentBranchID::run(),
            'reference' => $pickup->reference,
            'name' => $pickup->name,
            'picker_note' => $data['picker_note'],
            'address' => $pickup->address,
            'pickup_date' => $pickup->pickup_date,
            'auth' => $pickup->auth ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
