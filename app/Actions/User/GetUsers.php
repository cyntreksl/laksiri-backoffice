<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUsers
{
    use AsAction;

    public function handle(array $withoutRoles = []): Collection|array
    {
        $query = User::query()->withoutRole('driver');

        if ($withoutRoles) {
            $query->withoutRole($withoutRoles);
        }

        return $query->with(['branches', 'primaryBranch'])
            ->latest()
            ->get();
    }
}
