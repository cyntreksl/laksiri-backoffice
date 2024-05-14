<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalPickupCount
{
    use AsAction;

    public function handle(): int
    {
        return PickUp::count();
    }
}
