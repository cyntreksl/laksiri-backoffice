<?php

namespace App\Actions\DriverAreas;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Area;
use App\Models\Zone;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDriverAreas
{
    use AsAction;

    public function handle(array $data)
    {
        $driverArea = Area::create([
            'name' => $data['name'],
            'branch_id' => GetUserCurrentBranchID::run(),
        ]);

        foreach ($data['zone_ids'] as $id) {
            $zone = Zone::find($id);
            $zone->areas()->attach($driverArea->id);
        }

        return $driverArea;
    }
}
