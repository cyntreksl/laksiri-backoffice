<?php

namespace App\Repositories;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\GetRoles;
use App\Actions\Role\Permission\AssignPermissions;
use App\Actions\Role\Permission\GetAllPermissions;
use App\Actions\Role\Permission\GetPermissionGroup;
use App\Actions\Role\Permission\GetPermissionsByGroupName;
use App\Actions\Role\UpdateRole;
use App\Interfaces\RoleRepositoryInterface;
use App\Support\RoleHierarchy;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function getRoles()
    {
        return GetRoles::run();
    }

    public function getPermissionGroups()
    {
        return GetPermissionGroup::run(Auth::user());
    }

    public function getPermissionsByGroupName(string $group_name)
    {
        $permissions = GetPermissionsByGroupName::run($group_name);

        return response()->json($permissions);
    }

    public function roleHasPermissions($role, $permissions)
    {
        $hasPermission = true;
        foreach ($permissions as $permission) {
            if (! $role->hasPermissionTo($permission->name)) {
                $hasPermission = false;

                return $hasPermission;
            }
        }

        return $hasPermission;
    }

    public function getPermissions()
    {
        // Pass the authenticated user to GetAllPermissions
        return GetAllPermissions::run(Auth::user());
    }

    public function storeRole(array $data)
    {
        $actorRole = Auth::user()?->roles()->first();
        $targetHierarchy = isset($data['hierarchy']) ? (float) $data['hierarchy'] : 100.00;

        if ($actorRole && ! RoleHierarchy::canActOn($actorRole, $targetHierarchy)) {
            abort(403, 'You are not allowed to create a role with higher privilege.');
        }

        $role = CreateRole::run($data['name']);

        // Persist hierarchy if provided
        if (array_key_exists('hierarchy', $data)) {
            $role->hierarchy = (float) $data['hierarchy'];
            $role->save();
        }

        AssignPermissions::run($role, $data['permissions']);
    }

    public function deleteRole(Role $role)
    {
        $actorRole = Auth::user()?->roles()->first();

        if ($actorRole && ! RoleHierarchy::canActOn($actorRole, $role)) {
            abort(403, 'You are not allowed to delete this role.');
        }

        DeleteRole::run($role);
    }

    public function updateRole(array $data, Role $role)
    {
        $actorRole = Auth::user()?->roles()->first();
        $targetHierarchy = isset($data['hierarchy']) ? (float) $data['hierarchy'] : (float) ($role->hierarchy ?? 100.00);

        if ($actorRole && ! RoleHierarchy::canActOn($actorRole, $targetHierarchy)) {
            abort(403, 'You are not allowed to update this role to a higher privilege.');
        }

        $role = UpdateRole::run($data['name'], $role);

        // Update hierarchy if provided
        if (array_key_exists('hierarchy', $data)) {
            $role->hierarchy = (float) $data['hierarchy'];
            $role->save();
        }

        AssignPermissions::run($role, $data['permissions']);
    }
}
