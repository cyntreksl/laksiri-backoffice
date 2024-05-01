<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUsers
{
    use AsAction;

    public function handle(): Collection|array
    {
        return User::latest()->get();
    }
}
