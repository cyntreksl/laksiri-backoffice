<?php

namespace App\Actions\PackagePrice;

use App\Models\PackagePrice;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagePriceRules
{
    use AsAction;

    public function handle(): Collection|array
    {
        return PackagePrice::join('branches', 'branch_package_prices.destination_branch_id', '=', 'branches.id')
            ->select('branch_package_prices.*', 'branches.name as destination_branch_name')
            ->get();
    }
}
