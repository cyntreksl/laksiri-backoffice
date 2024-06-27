<?php

namespace App\Actions\DriverAreas;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDriverAreas
{
    use AsAction;

    public function handle(array $data)
    {
        $data['branch_id'] = GetUserCurrentBranchID::run();
        $driverarea = Area::create($data);

        return $driverarea;
    }
}
