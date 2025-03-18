<?php

namespace App\Repositories;

use App\Actions\PickupType\CreatePickupType;
use App\Actions\PickupType\DeletePickupType;
use App\Actions\PickupType\GetPickupTypes;
use App\Actions\PickupType\UpdatePickupType;
use App\Interfaces\PickupTypeRepositoryInterface;
use App\Models\PickupType;

class PickupTypeRepository implements PickupTypeRepositoryInterface
{
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
