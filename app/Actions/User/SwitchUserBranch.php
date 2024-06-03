<?php

namespace App\Actions\User;

use App\Models\Branch;
use Lorisleiva\Actions\Concerns\AsAction;
use function auth;

class SwitchUserBranch
{
    use AsAction;

    public function handle(Branch $branch): array
    {
        auth()->user()->update(['primary_branch_id' => $branch->id]);
        session(['current_branch_id' => $branch->id]);
        session(['current_branch_name' => $branch->name]);

        return [
            'branchName' => $branch->name,
            'branchId' => $branch->id,
        ];
    }
}
