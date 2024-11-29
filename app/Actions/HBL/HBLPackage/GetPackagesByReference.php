<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagesByReference
{
    use AsAction;

    public function handle(string $reference)
    {
        $hbl = HBL::withoutGlobalScopes()->with([
            'packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            },
        ])
            ->where('reference', $reference)->firstOrFail();

        $packages = $hbl->packages;

        return response()->json($packages);
    }
}
