<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLStatusByReference
{
    use AsAction;

    public function handle(string $reference)
    {
        $hbl = HBL::withoutGlobalScope(BranchScope::class)
            ->where('reference', $reference)
            ->firstOrFail();

        return response()->json($hbl->statusLogs);
    }
}
