<?php

namespace App\Repositories;

use App\Actions\PickupType\CreatePickupType;
use App\Actions\PickupType\DeletePickupType;
use App\Actions\PickupType\GetPickupTypes;
use App\Actions\PickupType\UpdatePickupType;
use App\Http\Resources\PickupTypeResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupTypeRepositoryInterface;
use App\Models\PickupType;
use Illuminate\Http\JsonResponse;

class PickupTypeRepository implements GridJsInterface, PickupTypeRepositoryInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = PickupType::query();

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $pickup_types = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PickupTypeResource::collection($pickup_types),
            'meta' => [
                'total' => $pickup_types->total(),
                'current_page' => $pickup_types->currentPage(),
                'perPage' => $pickup_types->perPage(),
                'lastPage' => $pickup_types->lastPage(),
            ],
        ]);

    }

    public function getPickupTypes()
    {
        try {
            return GetPickupTypes::run();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get pickup types: '.$e->getMessage());
        }
    }

    public function storePickupType(array $data)
    {
        try {
            return CreatePickupType::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create pickup Type: '.$e->getMessage());
        }
    }

    public function destroyPickupType(PickupType $pickupType)
    {
        try {
            return DeletePickupType::run($pickupType);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete exception name: '.$e->getMessage());
        }
    }

    public function updatePickupType(PickupType $pickupType, array $data)
    {
        try {
            return UpdatePickupType::run($pickupType, $data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update pickup type: '.$e->getMessage());
        }
    }
}
