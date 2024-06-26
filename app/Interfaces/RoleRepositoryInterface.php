<?php

namespace App\Interfaces;

interface RoleRepositoryInterface
{
    public function getRoles();

    public function getPermissions();

    public function getPermissionGroups();

    public function getPermissionsByGroupName(string $group_name);

    public function roleHasPermissions($role, $permissions);
}
