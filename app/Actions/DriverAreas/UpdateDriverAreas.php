<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDriverAreas
{
    use AsAction;

    public function handle(array $data)
    {

        $DriverAreas = Area::find($data['id']);

        $DriverAreas->update([
            'name' => $data['name'],
        ]);

        return $DriverAreas;

    }
}
