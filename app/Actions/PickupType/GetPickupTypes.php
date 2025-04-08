<?php

namespace App\Actions\PickupType;

use App\Models\PickupType;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupTypes
{
    use AsAction;

    public function handle()
    {
        return PickupType::all();
    }
}
