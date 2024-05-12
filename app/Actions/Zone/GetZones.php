<?php

namespace App\Actions\Zone;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Zone;
use Lorisleiva\Actions\Concerns\AsAction;

class GetZones
{
    use AsAction;

    public function handle()
    {
        return Zone::where('branch_id',GetUserCurrentBranchID::run())->latest()->with(['areas'])->get();
    }
}
