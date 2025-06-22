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
        $role = CreateRole::run($data['name']);

        AssignPermissions::run($role, $data['permissions']);
    }

    public function deleteRole(Role $role)
    {
        DeleteRole::run($role);
    }

    public function updateRole(array $data, Role $role)
    {
        $role = UpdateRole::run($data['name'], $role);

        AssignPermissions::run($role, $data['permissions']);
    }
}
