<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDriverAreas
{
    use AsAction;

    public function handle(Area $driverAreas)
    {
        $driverAreas->zones()->detach();

        $driverAreas->delete();
    }
}
