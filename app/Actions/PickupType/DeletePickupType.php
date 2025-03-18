<?php

namespace App\Actions\PickupType;

use App\Models\PickupType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePickupType
{
    use AsAction;

    public function handle(PickupType $pickupType): void
    {
        $pickupType->delete();
    }
}
