<?php

namespace App\Repositories\CallCenter;

use App\Actions\Delivery\CreateHBLDelivery;
use App\Actions\HBL\MarkAsDriverAssigned;
use App\Http\Resources\HBLDeliverResource;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;
use App\Models\HBLDeliver;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class DeliveryRepository implements DeliveryRepositoryInterface
{
    use ResponseAPI;

    public function assignDriverToDeliver(array $data): void
    {
        $hbl_ids = [];
        foreach ($data['job_ids'] as $job_id) {
            $hbl_ids[] = $job_id['id'];
        }
        MarkAsDriverAssigned::run($hbl_ids);

        CreateHBLDelivery::run($data['driver_id'], $hbl_ids);
    }

    public function getPendingDeliverForDriver(): JsonResponse
    {
        try {
            $query = HBLDeliver::query()->assignedToDriver()->with('hbl');

            $delivers = $query->orderBy('deliver_order')->get();

            $pendingDeliverResource = HBLDeliverResource::collection($delivers);

            return $this->success('Pending deliver list received successfully!', $pendingDeliverResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
