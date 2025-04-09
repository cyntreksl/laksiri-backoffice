<?php

namespace App\Interfaces;

use App\Models\PickupType;

interface PickupTypeRepositoryInterface
{
    public function getPickupTypes();

    public function storePickupType(array $data);

    public function destroyPickupType(PickupType $pickupType);

    public function updatePickupType(PickupType $pickupType, array $data);
}
