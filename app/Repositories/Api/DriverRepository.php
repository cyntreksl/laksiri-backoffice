<?php

namespace App\Repositories\Api;

use App\Actions\Driver\DriverLocation\CreateDriverLocation;
use App\Actions\Driver\UpdateDriverApi;
use App\Interfaces\Api\DriverRepositoryInterface;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverRepository implements DriverRepositoryInterface
{
    use ResponseAPI;

    public function updateDriver(Request $data): JsonResponse
    {
        try {

            UpdateDriverApi::run($data);

            return $this->success('Driver updated successfully!', [], 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function createDriverLocation(User $user, array $data): JsonResponse
    {
        try {
            CreateDriverLocation::run($user, $data);

            return $this->success('Driver location created successfully!', [], 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function updatePassword(array $data): bool
    {
        $driverId = auth()->user()->id;
        $driver = User::find($driverId);

        if (! $driver) {
            throw new \Exception('User not found.');
        }

        if (! Hash::check($data['old_password'], $driver->password)) {
            return false;
        }

        $driver->password = Hash::make($data['new_password']);

        return $driver->save();
    }
}
