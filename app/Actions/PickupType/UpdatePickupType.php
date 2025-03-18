<?php

namespace App\Actions\PickupType;

use App\Models\PickupType;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePickupType
{
    use AsAction;

    public function handle(PickupType $pickupType, array $data)
    {
        $pickupType->update($data);
    }
}
