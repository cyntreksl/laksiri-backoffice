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
            $data['primary_branch_id'] = auth()->user()->primary_branch_id;
            $data['created_by'] = auth()->id();
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
