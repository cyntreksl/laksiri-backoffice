<?php

namespace App\Actions\Branch;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserBranches
{
    use AsAction;

    public function handle(): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        $branches = $user->branches;
        $userPrimaryBranch = $user->primaryBranch;

        // If user has no branches in pivot table but has a primary branch, return primary branch
        if ($branches->isEmpty() && $userPrimaryBranch) {
            return collect([$userPrimaryBranch]);
        }

        // If primary branch exists and is not in the branches collection, add it
        if ($userPrimaryBranch && !$branches->contains('id', $userPrimaryBranch->id)) {
            $branches = $branches->push($userPrimaryBranch);
        }

        return $branches;
    }
}
