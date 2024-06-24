<?php

namespace App\Actions\DriverAreas;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllDriverAreas
{
    use AsAction;

    public function handle()
    {
        return Area::where('branch_id', GetUserCurrentBranchID::run())->latest()->get();
    }
}
