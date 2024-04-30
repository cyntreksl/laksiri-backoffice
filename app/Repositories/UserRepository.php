<?php

namespace App\Repositories;

use App\Actions\User\CreateUser;
use App\Actions\User\GetUsers;
use App\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function getUsers()
    {
        return GetUsers::run();
    }

    public function storeUser(array $data)
    {
        return CreateUser::run($data);
    }
}
