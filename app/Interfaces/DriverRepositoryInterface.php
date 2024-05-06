<?php

namespace App\Interfaces;

interface DriverRepositoryInterface
{
    public function getDrivers();

    public function storeDriver(array $data);
}
