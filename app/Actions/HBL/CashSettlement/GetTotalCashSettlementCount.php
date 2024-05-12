<?php

namespace App\Actions\HBL\CashSettlement;

use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalCashSettlementCount
{
    use AsAction;

    public function handle(): int
    {
       return HBL::cashSettlement()->count();
    }
}
