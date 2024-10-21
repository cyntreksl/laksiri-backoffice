<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPriceRulesByCargoModeAndHBLType
{
    use AsAction;

    public function handle(string $cargo_mode, string $hbl_type, int $destination_branch_id): Collection|array
    {
        $price_mode = $cargo_mode === 'Sea Cargo' ? 'volume' : 'weight';

        return BranchPrice::join('branches', 'branch_prices.branch_id', '=', 'branches.id')
            ->select('branch_prices.*', 'branches.name as destination_branch_name')
            ->where('cargo_mode', $cargo_mode)
            ->where('hbl_type', $hbl_type)
            ->where('price_mode', $price_mode)
            ->where('destination_branch_id', $destination_branch_id)->get();

    }
}
