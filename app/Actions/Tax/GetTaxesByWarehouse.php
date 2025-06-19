<?php

namespace App\Actions\Tax;

use App\Models\Scopes\BranchScope;
use App\Models\Tax;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTaxesByWarehouse
{
    use AsAction;

    public function handle(int $warehouseID): Collection
    {
        return Tax::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $warehouseID)
            ->where('is_active', true)
            ->get();
    }
}
