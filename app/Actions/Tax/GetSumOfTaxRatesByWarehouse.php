<?php

namespace App\Actions\Tax;

use App\Models\Scopes\BranchScope;
use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSumOfTaxRatesByWarehouse
{
    use AsAction;

    public function handle(int $warehouseID): float
    {
        return Tax::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $warehouseID)
            ->where('is_active', true)
            ->sum('rate');
    }
}
