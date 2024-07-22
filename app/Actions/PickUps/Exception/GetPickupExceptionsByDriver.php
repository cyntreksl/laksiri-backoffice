<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupExceptionsByDriver
{
    use AsAction;

    public function handle()
    {
        return PickupException::assignedToDriver()
            ->get();
    }
}
