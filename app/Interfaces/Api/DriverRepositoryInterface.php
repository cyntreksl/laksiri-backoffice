<?php

namespace App\Interfaces\Api;

use App\Models\User;
use Illuminate\Http\Request;

interface DriverRepositoryInterface
{
    public function updateDriver(Request $data);

    public function createDriverLocation(User $user, array $data);

    public function updatePassword(array $data);

}
