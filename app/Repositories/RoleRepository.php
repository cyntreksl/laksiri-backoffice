<?php

namespace App\Repositories;

use App\Actions\Role\GetRoles;
use App\Actions\Role\Permission\GetAllPermissions;
use App\Actions\Role\Permission\GetPermissionGroup;
use App\Actions\Role\Permission\GetPermissionsByGroupName;
use App\Interfaces\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function getRoles()
    {
        return GetRoles::run();
    }

    public function getPermissionGroups()
    {
        return GetPermissionGroup::run();
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
        return GetAllPermissions::run();
    }
}
