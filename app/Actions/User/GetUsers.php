<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUsers
{
    use AsAction;

    public function handle()
    {
        return User::latest()->get();
    }
}
