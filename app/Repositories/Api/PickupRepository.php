<?php

namespace App\Repositories\Api;

use App\Actions\PickUps\GetPickupsByDriver;
use App\Http\Resources\PickupResource;
use App\Interfaces\Api\PickupRepositoryInterface;
use App\Models\PickUp;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class PickupRepository implements PickupRepositoryInterface
{
    use ResponseAPI;

    public function getPendingPickupsForDriver(): JsonResponse
    {
        try {
            $pickups = GetPickupsByDriver::run();

            // Transform pickups into resource format
            $pendingPickupsResource = PickupResource::collection($pickups);

            return $this->success('Pending pickup list received successfully!', $pendingPickupsResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function showPickup(PickUp $pickup): JsonResponse
    {
        try {
            $pickupResource = new PickupResource($pickup);
            return $this->success('Success', $pickupResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
