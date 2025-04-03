<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLsByIDs
{
    use AsAction;

    public function handle(array $value): Collection
    {
        return HBL::query()->whereIn('id', $value)->get();
    }
}
