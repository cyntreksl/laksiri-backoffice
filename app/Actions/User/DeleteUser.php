<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;

    public function handle(User $user): void
    {
        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
