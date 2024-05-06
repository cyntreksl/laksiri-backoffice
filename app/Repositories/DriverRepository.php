<?php

namespace App\Repositories;

use App\Actions\Driver\CreateDriver;
use App\Interfaces\DriverRepositoryInterface;

class DriverRepository implements DriverRepositoryInterface
{
    public function getDrivers()
    {
        // TODO: Implement getDrivers() method.
    }

    public function storeDriver(array $data)
    {
        return CreateDriver::run($data);
    }
}
