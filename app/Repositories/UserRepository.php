<?php

namespace App\Repositories;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\GetUsers;
use App\Actions\User\UpdateUser;
use App\Actions\User\UpdateUserBranch;
use App\Actions\User\UpdateUserPassword;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

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

    public function updateUser(array $data, User $user)
    {
        return UpdateUser::run($data, $user);
    }

    public function deleteUser(User $user): void
    {
        DeleteUser::run($user);
    }

    public function updatePassword(array $data, User $user): void
    {
        UpdateUserPassword::run($data, $user);
    }

    public function updateBranch(array $data, User $user): void
    {
        UpdateUserBranch::run($data, $user);
    }
}
