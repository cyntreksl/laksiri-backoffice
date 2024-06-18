<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetExceptionsByIds
{
    use AsAction;

    public function handle(array $value): Collection
    {
        return PickupException::whereIn('id', $value)->get();
    }
}
