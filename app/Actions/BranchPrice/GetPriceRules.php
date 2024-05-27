<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPriceRules
{
    use AsAction;

    public function handle(): Collection|array
    {
        return BranchPrice::join('branches', 'branch_prices.destination_branch_id', '=', 'branches.id')
            ->select('branch_prices.*', 'branches.name as destination_branch_name')
            ->get();
    }
}
