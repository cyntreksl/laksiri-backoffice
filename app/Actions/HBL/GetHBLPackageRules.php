<?php

namespace App\Actions\HBL;

use App\Models\PackagePrice;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLPackageRules
{
    use AsAction;

    public function handle(string $cargo_mode, string $hbl_type, int $destination_branch_id)
    {
        return PackagePrice::join('branches', 'branch_package_prices.branch_id', '=', 'branches.id')
            ->select('branch_package_prices.*', 'branches.name as destination_branch_name')
            ->where('cargo_mode', $cargo_mode)
            ->where('hbl_type', $hbl_type)
            ->where('destination_branch_id', $destination_branch_id)
            ->get();
    }
}
