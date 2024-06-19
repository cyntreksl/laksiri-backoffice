<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteException
{
    use AsAction;

    public function handle(PickupException $pickupException): void
    {
        $pickupException->delete();
    }
}
