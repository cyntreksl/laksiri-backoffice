<?php

namespace App\Repositories;

use App\Actions\Driver\CreateDriver;
use App\Actions\Driver\GetDrivers;
use App\Interfaces\DriverRepositoryInterface;

class DriverRepository implements DriverRepositoryInterface
{
    public function getAllDrivers()
    {
        return GetDrivers::run();
    }

    public function storeDriver(array $data)
    {
        return CreateDriver::run($data);
    }
}
