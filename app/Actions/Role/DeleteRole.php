<?php

namespace App\Actions\Role;

use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    use AsAction;

    public function handle(Role $role)
    {
        if ($role->name !== 'admin') {
            $role->delete();
        }
    }
}
