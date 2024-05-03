<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserPassword
{
    use AsAction;

    public function handle(array $data, User $user): void
    {
        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();
    }
}
