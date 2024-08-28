<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPriceRulesByCargoModeAndHBLType
{
    use AsAction;

    public function handle(string $cargo_mode, string $hbl_type): BranchPrice
    {
        return BranchPrice::join('branches', 'branch_prices.destination_branch_id', '=', 'branches.id')
            ->select('branch_prices.*', 'branches.name as destination_branch_name')
            ->where('cargo_mode', $cargo_mode)
            ->where('hbl_type', $hbl_type)
            ->first();
    }
}
