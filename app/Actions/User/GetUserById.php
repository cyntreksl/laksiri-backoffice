<?php

namespace App\Actions\User;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserById
{
    use AsAction;

    public function handle(int $userId): User
    {
        return User::withoutGlobalScopes()->findOrFail($userId);
    }
}
