<?php

namespace App\Repositories\Api;

use App\Actions\PickUps\ConvertPickupToHBL;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\Exception\CreatePickUpException;
use App\Actions\PickUps\Exception\GetPickupExceptionsByDriver;
use App\Actions\PickUps\GetPickupsByDriver;
use App\Enum\PickupStatus;
use App\Http\Resources\PickupExceptionResource;
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

    public function pickupToHbl($pickUp, $request): JsonResponse
    {
        try {
            $hbl = ConvertPickupToHBL::run($pickUp, $request);

            return $this->success('Pickup converted to HBL successfully!', []);

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

    public function storePickup(array $data): JsonResponse
    {
        try {
            $pickup = CreatePickUp::run($data);

            return $this->success('Pickup created successfully!', $pickup);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode());
        }
    }

    public function savePickupException(array $data, PickUp $pickup): JsonResponse
    {
        try {
            $pickupException = CreatePickUpException::run($data, $pickup);

            return $this->success('Pickup exception created successfully!', $pickupException);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function completedPickupWithHBL(): JsonResponse
    {
        try {
            $pickups = PickUp::where('status', PickupStatus::COLLECTED)->with('hbl')->get();

            // Transform pickups into resource format
            $completedPickupsResource = PickupResource::collection($pickups);

            return $this->success('Completed pickup list received successfully!', $completedPickupsResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getPickupExceptionsForDriver(): JsonResponse
    {
        try {
            $pickupExceptions = GetPickupExceptionsByDriver::run();

            // Transform pickup exception into resource format
            $pickupExceptionResource = PickupExceptionResource::collection($pickupExceptions);

            return $this->success('Pickup exception list received successfully!', $pickupExceptionResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
