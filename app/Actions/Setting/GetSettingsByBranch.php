<?php

namespace App\Actions\Setting;

use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSettingsByBranch
{
    use AsAction;

    public function handle(int $branchId)
    {
        return Setting::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->with('branch')
            ->first();
    }
}
