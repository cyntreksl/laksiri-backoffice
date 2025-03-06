<?php

namespace App\Interfaces;

use App\Models\HBL;
use App\Models\PickUp;

interface NotificationMailRepositoryInterface
{
    public function sendPickupCreationNotification(PickUp $pickUp);

    public function sendAssignDriverNotification(PickUp $pickUp);

    public function sendCollectedCargoNotification(PickUp $pickUp);

    public function sendCashReceivedNotification(HBL $hbl);

    public function sendShipmentDepartureNotification(array $data);

    public function sendHBLReleasedNotification(HBL $hbl);
}
