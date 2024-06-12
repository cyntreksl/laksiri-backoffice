<?php

namespace App\Actions\Driver;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDriverPassword
{
    use AsAction;

    public function handle(array $data, User $user): User
    {

        // update driver password information
        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return $user;
    }
}
