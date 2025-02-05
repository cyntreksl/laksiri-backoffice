<?php

namespace App\Interfaces;

use App\Models\PickUp;

interface NotificationMailRepositoryInterface
{
    public function sendPickupCreationNotification(PickUp $pickUp);

    public function sendAssignDriverNotification(PickUp $pickUp);

    public function sendCollectedCargoNotification(PickUp $pickUp);
}
