<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePickup
{
    use AsAction;

    public function handle(PickUp $pickup): void
    {
        $pickup->delete();
    }
}
