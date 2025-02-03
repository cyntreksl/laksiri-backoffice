<?php

namespace App\Actions\Role;

use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class CreateRole
{
    use AsAction;

    public function handle(string $role_name): Role
    {
        $role = new Role;
        $role->name = $role_name;
        $role->guard_name = 'web';
        $role->save();

        return $role;
    }
}
