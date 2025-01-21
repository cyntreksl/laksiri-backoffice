<?php

namespace App\Interfaces\CallCenter;

interface DeliveryRepositoryInterface
{
    public function assignDriverToDeliver(array $data);

    public function getPendingDeliverForDriver();
}
