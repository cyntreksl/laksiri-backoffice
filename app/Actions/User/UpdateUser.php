<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUser
{
    use AsAction;

    public function handle(array $data, User $user): User
    {
        // update user information
        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);

        if (isset($data['role_id'])) {
            // remove all existing roles
            $user->roles()->detach();

            // assign a new role to the user
            $user->assignRole($data['role_id']);
        }

        return $user;
    }
}
