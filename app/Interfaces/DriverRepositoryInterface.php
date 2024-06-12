<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DriverRepositoryInterface
{
    public function getAllDrivers(): Collection;

    public function storeDriver(array $data);

    public function updateDriverDetails(array $data, User $user);

    public function updateDriverPassword(array $data, User $user);
}
