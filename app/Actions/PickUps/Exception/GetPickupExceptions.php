<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupExceptions
{
    use AsAction;

    public function handle()
    {
        return PickupException::latest()->get();
    }
}
