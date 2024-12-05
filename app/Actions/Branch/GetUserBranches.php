<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserBranches
{
    use AsAction;

    public function handle(): Collection|array
    {
        $user = Auth::user();
        $branches = $user->branches;
        $userPrimaryBranch = $user->primaryBranch;

        if (!$branches->contains($userPrimaryBranch->id)) {
            $branches = $branches->merge([$userPrimaryBranch]);
        }
        return $branches;
    }
}
