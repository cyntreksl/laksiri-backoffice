<?php

namespace App\Actions\WarehouseZone;

use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehouseZones
{
    use AsAction;

    public function handle()
    {
        return WarehouseZone::all();
    }
}
