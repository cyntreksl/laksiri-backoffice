<?php

namespace App\Repositories\Api;

use App\Actions\PickUps\ConvertPickupToHBL;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\Exception\CreatePickUpException;
use App\Actions\PickUps\Exception\GetPickupExceptionsByDriver;
use App\Enum\PickupStatus;
use App\Http\Resources\PickupExceptionResource;
use App\Http\Resources\PickupResource;
use App\Interfaces\Api\PickupRepositoryInterface;
use App\Models\PickUp;
use App\Models\PickupException;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class PickupRepository implements PickupRepositoryInterface
{
    use ResponseAPI;

    private function buildPendingPickupsQuery(array $data)
    {
        $query = PickUp::query()->assignedToDriver();

        $this->applyDateFilter($query, $data);
        $this->applyStatusFilter($query);
        $this->applyReferenceNumberFilter($query, $data);
        $this->applyMobileNumberFilter($query, $data);
        $this->applyNameFilter($query, $data);

        $query->whereDoesntHave('pickupException');

        return $query;
    }

    private function applyDateFilter($query, array $data)
    {
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $query->whereBetween('pickup_date', [$data['start_date'], $data['end_date']]);
        }
    }

    private function applyStatusFilter($query)
    {
        $query->whereIn('system_status', [
            PickUp::SYSTEM_STATUS_PICKUP_CREATED,
            PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED,
        ]);
    }

    private function applyReferenceNumberFilter($query, array $data)
    {
        if (isset($data['reference_number'])) {
            $query->where('reference_number', $data['reference_number']);
        }
    }

    private function applyMobileNumberFilter($query, array $data)
    {
        if (isset($data['mobile_number'])) {
            $query->where('mobile_number', $data['mobile_number']);
        }
    }

    private function applyNameFilter($query, array $data)
    {
        if (isset($data['name'])) {
            $query->where('name', 'like', '%'.$data['name'].'%');
        }
    }

    public function getPendingPickupsForDriver(array $data): JsonResponse
    {
        try {
            $query = $this->buildPendingPickupsQuery($data);
            $pickups = $query->orderBy('id', 'desc')->orderBy('pickup_order')->get();
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

            return $this->success('Pickup converted to HBL successfully!', $hbl);

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

    public function completedPickupWithHBL(array $data): JsonResponse
    {
        try {
            $query = PickUp::query()->where('status', PickupStatus::COLLECTED);

            if (isset($data['start_date']) && isset($data['end_date'])) {
                $query->whereBetween('pickup_date', [$data['start_date'], $data['end_date']]);
            }

            $pickups = $query->where('driver_id', auth()->id())
                ->with('hbl')
                ->orderBy('pickup_date', 'desc')
                ->get();

            // Transform pickups into resource format
            $completedPickupsResource = PickupResource::collection($pickups);

            return $this->success('Completed pickup list received successfully!', $completedPickupsResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getPickupExceptionsForDriver(array $data): JsonResponse
    {
        try {
            $pickupExceptions = GetPickupExceptionsByDriver::run($data);

            // Transform pickup exception into resource format
            $pickupExceptionResource = PickupExceptionResource::collection($pickupExceptions);

            return $this->success('Pickup exception list received successfully!', $pickupExceptionResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function showPickupException(int $exceptionId): JsonResponse
    {
        try {
            $data = PickUpException::findOrFail($exceptionId);
            $resourceData = new PickupExceptionResource($data);

            return $this->success('Pickup exception details received successfully!', $resourceData);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
