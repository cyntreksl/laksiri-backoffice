<?php

namespace App\Repositories\Api;

use App\Actions\PickUps\ConvertPickupToHBL;
use App\Actions\PickUps\GetPickupsByDriver;
use App\Http\Resources\PickupResource;
use App\Interfaces\Api\PickupRepositoryInterface;
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

    public function pickupToHbl($pickUp, $request): JsonResponse
    {
        try {
            $hbl = ConvertPickupToHBL::run($pickUp, $request);

            return $this->success('Pickup converted to HBL successfully!',[]);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
