<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    public function handle(array $data): User
    {
        if ($data['role'] === 'driver') {
            $data['primary_branch_id'] = GetUserCurrentBranchID::run();
            $data['created_by'] = auth()->id();
        }

        if (empty($data['preferred_zone'])) {
            $data['preferred_zone'] = null;
        } else {
            // Convert preferred_zone array to comma-separated string
            $data['preferred_zone'] = implode(',', $data['preferred_zone']);
        }

        $user = User::create($data);

        // assign role
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        if (isset($data['secondary_branches'])) {
            $user->branches()->attach($data['secondary_branches']);
        }

        return $user;
    }
}
