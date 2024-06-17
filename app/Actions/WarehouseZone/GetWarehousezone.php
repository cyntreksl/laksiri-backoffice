<?php

namespace App\Actions\WarehouseZone;

use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehousezone
{
    use AsAction;

    public function handle($id)
    {
        return WarehouseZone::find($id);
    }
}
