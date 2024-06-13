<?php

namespace App\Actions\Driver;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDriver
{
    use AsAction;

    public function handle(User $user): void
    {

        $user->delete();
    }
}
