<?php

namespace App\Actions\Driver;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDrivers
{
    use AsAction;

    public function handle(): Collection|array
    {
        return User::role('driver')
            ->latest()
            ->get();
    }
}
