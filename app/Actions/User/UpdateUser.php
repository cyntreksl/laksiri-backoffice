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
            // Prevent assigning super-admin role unless actor is super-admin
            $targetRole = \Spatie\Permission\Models\Role::find($data['role_id']);
            if ($targetRole && $targetRole->name === 'super-admin' && ! auth()->user()->hasRole('super-admin')) {
                abort(403, 'Only Super Admin can assign the Super Admin role to users.');
            }

            // remove all existing roles
            $user->roles()->detach();

            // assign a new role to the user
            $user->assignRole($data['role_id']);

            activity()->performedOn($user)
                ->withProperties(['role_id' => $data['role_id']])
                ->event('updated')
                ->log('updated');
        }

        return $user;
    }
}
