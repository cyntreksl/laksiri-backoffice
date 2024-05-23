<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalPickupExceptionCount
{
    use AsAction;

    public function handle(): int
    {
        return PickupException::count();
    }
}
