<?php

namespace App\Actions\WarehouseZone;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\WarehouseZone;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWarehousezone
{
    use AsAction;

    public function handle(array $data)
    {
        $data['branch_id'] = GetUserCurrentBranchID::run();
        $warehousezone = WarehouseZone::create($data);

        return $warehousezone;
    }
}
