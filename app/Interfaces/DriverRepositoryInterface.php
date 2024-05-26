<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface DriverRepositoryInterface
{
    public function getAllDrivers(): Collection;

    public function storeDriver(array $data);
}
