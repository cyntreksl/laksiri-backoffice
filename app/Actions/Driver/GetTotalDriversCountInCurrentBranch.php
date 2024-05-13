<?php

namespace App\Actions\Driver;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalDriversCountInCurrentBranch
{
    use AsAction;

    public function handle(): int
    {
        return User::role('driver')->currentBranch()->count();
    }
}
