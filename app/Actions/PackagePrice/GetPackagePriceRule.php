<?php

namespace App\Actions\PackagePrice;

use App\Models\Branch;
use App\Models\PackagePrice;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagePriceRule
{
    use AsAction;

    public function handle($packageRuleId): PackagePrice
    {
        $branch_type = Branch::where('id', Auth::user()->primary_branch_id)->pluck('type')->first();
        if ($branch_type === 'Departure') {
            return PackagePrice::where('id', $packageRuleId)->first();
        } else {
            return PackagePrice::withoutGlobalScopes()->where('id', $packageRuleId)->first();
        }

    }
}
