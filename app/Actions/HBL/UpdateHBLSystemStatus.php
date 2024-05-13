<?php

namespace App\Actions\HBL;

use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLSystemStatus
{
    use AsAction;

    public function handle(HBL $HBL, float $status): HBL
    {
        $HBL->system_status = $status;
        $HBL->save();
        HBLSystemStatusLog::run($HBL,$status);

        return $HBL;
    }
}
