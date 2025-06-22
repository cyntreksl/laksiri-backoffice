<?php

namespace App\Actions\Role\Permission;

use App\Models\User;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Permission;

class GetAllPermissions
{
    use AsAction;

    public function handle(?User $user = null): Collection
    {
        if ($user && ! $user->hasRole(['admin', 'super-admin'])) {
            // If a non-admin user is provided, return only their permissions
            return $user->getAllPermissions();
        }

        // Otherwise, return all permissions (for admins or when no user is specified)
        return Permission::all();
    }
}
