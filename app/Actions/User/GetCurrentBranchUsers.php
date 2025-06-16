<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCurrentBranchUsers
{
    use AsAction;

    public function handle(array $withoutRoles = []): Collection|array
    {
        $query = User::query()
            ->where('primary_branch_id', GetUserCurrentBranchID::run())
            ->withoutRole('driver');

        if ($withoutRoles) {
            $query->withoutRole($withoutRoles);
        }

        return $query->with(['branches', 'primaryBranch'])
            ->latest()
            ->get();
    }
}
