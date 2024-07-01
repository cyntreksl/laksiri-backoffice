<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDriverAreas
{
    use AsAction;

    public function handle(array $data)
    {
        $driverArea = Area::find($data['id']);

        $driverArea->update([
            'name' => $data['name'],
        ]);

        $driverArea->zones()->sync($data['zone_ids']);

        return $driverArea;
    }
}
