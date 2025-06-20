<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByIdWithPackages
{
    use AsAction;

    public function handle($hbl)
    {
        return HBL::withoutGlobalScope(BranchScope::class)
            ->where('id', $hbl)
            ->withTrashed()
            ->withCount(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }])
            ->with(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }, 'latestRtfRecord'])
            ->withSum(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }], 'weight')
            ->withSum(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }], 'volume')
            ->first();
    }
}
