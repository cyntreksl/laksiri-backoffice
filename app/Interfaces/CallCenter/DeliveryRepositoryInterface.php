<?php

namespace App\Interfaces\CallCenter;

use App\Models\HBL;
use App\Models\HBLDeliver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface DeliveryRepositoryInterface
{
    public function assignDriverToDeliver(array $data);

    public function getPendingDeliverForDriver();

    public function showDeliver(HBLDeliver $hblDeliver): JsonResponse;

    public function getFilteredDelivers(Request $request);

    public function saveDeliveryOrder(array $deliveries);

    public function releaseDeliverOrder(array $data);

    public function unassignDriverFromDeliver(HBL $hbl): void;
}
