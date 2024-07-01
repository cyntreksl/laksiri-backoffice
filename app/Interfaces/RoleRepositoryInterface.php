<?php

namespace App\Interfaces;

use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function getRoles();

    public function getPermissions();

    public function getPermissionGroups();

    public function getPermissionsByGroupName(string $group_name);

    public function roleHasPermissions($role, $permissions);

    public function storeRole(array $data);

    public function updateRole(array $data, Role $role);

    public function deleteRole(Role $role);
}
