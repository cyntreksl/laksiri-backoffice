<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDriverAreaZoneIDs
{
    use AsAction;

    public function handle($id)
    {
        return Area::find($id)->zones->pluck('id');
    }
}
