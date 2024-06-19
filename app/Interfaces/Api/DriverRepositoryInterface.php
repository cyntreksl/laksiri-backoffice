<?php

namespace App\Interfaces\Api;

use Illuminate\Http\Request;

interface DriverRepositoryInterface
{
    public function updateDriver(Request $data);
}
