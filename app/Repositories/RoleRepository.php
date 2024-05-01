<?php

namespace App\Repositories;

use App\Actions\Role\GetRoles;
use App\Interfaces\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function getRoles()
    {
        return GetRoles::run();
    }
}
