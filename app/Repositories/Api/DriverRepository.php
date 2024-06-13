<?php

namespace App\Repositories\Api;

use App\Actions\Driver\UpdateDriverApi;
use App\Interfaces\Api\DriverRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
