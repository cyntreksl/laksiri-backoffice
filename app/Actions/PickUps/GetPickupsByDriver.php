<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupsByDriver
{
    use AsAction;

    public function handle()
    {
        return PickUp::assignedToDriver()
            ->orderBy('pickup_order')
            ->get();
    }
}
