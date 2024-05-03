<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserBranch
{
    use AsAction;

    public function handle(array $data, User $user): void
    {
        // update user primary branch
        $user->update([
            'primary_branch_id' => $data['primary_branch_id'],
        ]);

        if (isset($data['secondary_branches'])) {
            $user->branches()->detach();

            $user->branches()->attach($data['secondary_branches']);
        }
    }
}
