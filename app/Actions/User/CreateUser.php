<?php

namespace App\Actions\User;

use App\Enum\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    public function handle(array $data): User
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'primary_branch_id' => $data['primary_branch_id'],
            'status' => UserStatus::INVITED->value,
        ]);

        if (isset($data['role_id'])) {
            // assign role
            $user->assignRole($data['role_id']);
        }

        if (isset($data['secondary_branches'])) {
            $user->branches()->attach($data['secondary_branches']);
        }

        return $user;
    }
}
