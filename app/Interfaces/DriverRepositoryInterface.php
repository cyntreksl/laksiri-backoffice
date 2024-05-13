<?php

namespace App\Interfaces;

use App\Models\User;

interface DriverRepositoryInterface
{
    public function getAllDrivers(): User;

    public function storeDriver(array $data);
}
