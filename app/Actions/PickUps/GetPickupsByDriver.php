<?php

namespace App\Actions\PickUps;

use App\Enum\PickupStatus;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupsByDriver
{
    use AsAction;

    public function handle()
    {
        return PickUp::assignedToDriver()
            ->where('status', PickupStatus::PENDING)
            ->orderBy('pickup_order')
            ->get();
    }
}
