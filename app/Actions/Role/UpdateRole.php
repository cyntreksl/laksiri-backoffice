<?php

namespace App\Actions\Role;

use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    use AsAction;

    public function handle(string $role_name, Role $role): Role
    {
        $role->name = $role_name;
        $role->save();

        return $role;
    }
}
