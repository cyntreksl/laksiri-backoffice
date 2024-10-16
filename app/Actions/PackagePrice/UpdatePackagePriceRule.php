<?php

namespace App\Actions\PackagePrice;

use App\Models\BranchPrice;
use App\Models\PackagePrice;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePackagePriceRule
{
    use AsAction;

    public function handle(PackagePrice $packagePrice, array $data): PackagePrice
    {
        $packagePrice->update($data);
        return $packagePrice;
    }
}
