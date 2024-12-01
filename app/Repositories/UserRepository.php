<?php

namespace App\Repositories;

use App\Actions\Branch\GetBranchByName;
use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\GetUsers;
use App\Actions\User\SwitchUserBranch;
use App\Actions\User\UpdateUser;
use App\Actions\User\UpdateUserBranch;
use App\Actions\User\UpdateUserPassword;
use App\Exports\UsersExport;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class UserRepository implements UserRepositoryInterface
{
    public function getUsers(array $withoutRoles = [])
    {
        return GetUsers::run($withoutRoles);
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

    /**
     * @throws \Exception
     */
    public function switchBranch(string $branchName): mixed
    {
        try {
            $branchName = GetBranchByName::run($branchName);

            return SwitchUserBranch::run($branchName);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
