<?php

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getUsers();

    public function storeUser(array $data);

    public function deleteUser(User $user);
}
