<?php

namespace App\Repositories\CallCenter;

use App\Actions\Delivery\CreateHBLDelivery;
use App\Actions\HBL\MarkAsDriverAssigned;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;

class DeliveryRepository implements DeliveryRepositoryInterface
{
    public function assignDriverToDeliver(array $data): void
    {
        $hbl_ids = [];
        foreach ($data['job_ids'] as $job_id) {
            $hbl_ids[] = $job_id['id'];
        }
        MarkAsDriverAssigned::run($hbl_ids);

        CreateHBLDelivery::run($data['driver_id'], $hbl_ids);
    }
}
