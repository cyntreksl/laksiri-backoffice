<?php

namespace App\Actions\Role;

use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    use AsAction;

    public function handle(Role $role)
    {
        if (in_array($role->name, ['admin', 'super-admin']) && auth()->user() && ! auth()->user()->hasRole('super-admin')) {
            throw ValidationException::withMessages([
                'role' => 'You are not authorized to delete this role.',
            ])->status(403); // Forbidden
        }

        $role->delete();
    }
}
