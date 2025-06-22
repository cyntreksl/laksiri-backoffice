<?php

namespace App\Actions\Role;

use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    use AsAction;

    public function handle(string $role_name, Role $role): Role
    {
        if (in_array($role->name, ['admin', 'super-admin']) && auth()->user() && ! auth()->user()->hasRole('super-admin')) {
            throw ValidationException::withMessages([
                'role' => 'You are not authorized to modify this role.',
            ])->status(403);
        }

        $role->name = $role_name;
        $role->save();

        return $role;
    }
}
