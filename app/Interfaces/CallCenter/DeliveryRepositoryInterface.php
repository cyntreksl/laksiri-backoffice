<?php

namespace App\Interfaces\CallCenter;

use App\Models\HBLDeliver;
use Illuminate\Http\JsonResponse;

interface DeliveryRepositoryInterface
{
    public function assignDriverToDeliver(array $data);

    public function getPendingDeliverForDriver();

    public function showDeliver(HBLDeliver $hblDeliver): JsonResponse;
}
