<?php

namespace App\Actions\Role;

use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class GetRoles
{
    use AsAction;

    public function handle(): Collection|array
    {
        return Role::whereNot('name', 'driver')
            ->get();
    }
}
