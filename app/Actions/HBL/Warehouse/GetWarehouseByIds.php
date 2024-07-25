<?php

namespace App\Actions\HBL\Warehouse;

use App\Models\HBL;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehouseByIds
{
    use AsAction;

    public function handle(array $value): Collection
    {
        return HBL::warehouse()->whereIn('id', $value)->get();
    }
}
