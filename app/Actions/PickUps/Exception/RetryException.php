<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class RetryException
{
    use AsAction;

    public function handle(PickUp $pickup): void
    {
        $pickup->pickupException()->delete();

        $pickup->retry_attempts = $pickup->retry_attempts + 1;
        $pickup->save();
    }
}
