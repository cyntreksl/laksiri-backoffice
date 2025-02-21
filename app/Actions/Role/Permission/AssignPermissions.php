<?php

namespace App\Actions\Role\Permission;

use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPermissions
{
    use AsAction;

    public function handle(Role $role, $permissions): void
    {
        // get permission names
        $permissionsNames = Permission::whereIn('id', $permissions)->pluck('name');
        if (! empty($permissions)) {
            $role->syncPermissions($permissionsNames);
        }
        activity('role_permissions')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $permissions])
            ->log("Updated permissions for role: {$role->name}");
    }
}
