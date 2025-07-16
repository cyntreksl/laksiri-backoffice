<?php

namespace App\Actions\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranchID
{
    use AsAction;

    public function handle(): int
    {
        $branchId = session('current_branch_id');

        if (empty($branchId) || ! Auth::check() || ! Auth::user()) {
            $user = Auth::user();
            if ($user && $user->primary_branch_id) {
                $primaryBranch = Branch::find($user->primary_branch_id);
                $branchId = $primaryBranch ? $primaryBranch->id : null;
            } else {
                // Fallback for CLI: use first branch as default
                $primaryBranch = Branch::first();
                $branchId = $primaryBranch ? $primaryBranch->id : null;
            }
            if (! $branchId) {
                throw new \RuntimeException('No branch found for GetUserCurrentBranchID.');
            }
        }

        return $branchId;
    }
}
