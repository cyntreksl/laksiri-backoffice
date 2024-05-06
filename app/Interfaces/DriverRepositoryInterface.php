<?php

namespace App\Interfaces;

interface DriverRepositoryInterface
{
    public function getAllDrivers();

    public function storeDriver(array $data);
}
