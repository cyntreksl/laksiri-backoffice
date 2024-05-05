<?php

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getUsers();

    public function storeUser(array $data);

    public function updateUser(array $data, User $user);

    public function deleteUser(User $user);

    public function updatePassword(array $data, User $user);

    public function updateBranch(array $data, User $user);

    public function switchBranch(string $branchName);
}
