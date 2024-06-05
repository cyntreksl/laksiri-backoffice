<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupByIds
{
    use AsAction;

    public function handle(array $value): Collection
    {
        return PickUp::whereIn('id', $value)->get();
    }
}
