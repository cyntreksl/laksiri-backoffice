<?php

namespace App\Repositories;

use App\Actions\Driver\GetDrivers;
use App\Actions\User\CreateUser;
use App\Interfaces\DriverRepositoryInterface;

class DriverRepository implements DriverRepositoryInterface
{
    public function getAllDrivers()
    {
        return GetDrivers::run();
    }

    public function storeDriver(array $data)
    {
        return CreateUser::run($data);
    }
}
