<?php

namespace App\Actions\PackagePrice;

use App\Models\PackagePrice;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagePriceRule
{
    use AsAction;

    public function handle($packageRuleId): PackagePrice
    {
        return PackagePrice::where('id', $packageRuleId)->first();
    }
}
