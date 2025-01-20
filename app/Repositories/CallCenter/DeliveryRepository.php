<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\MarkAsDriverAssigned;
use App\Actions\PickUps\AssignDriver;
use App\Actions\PickUps\GetPickupByIds;
use App\Interfaces\CallCenter\DeliveryRepositoryInterface;

class DeliveryRepository implements DeliveryRepositoryInterface
{
    public function assignDriverToDeliver(array $data): void
    {
        $hbl_ids = [];
        foreach ($data['job_ids'] as $job_id) {
           $hbl_ids[] = $job_id['id'];
        }
        $t = MarkAsDriverAssigned::run($hbl_ids);
        dd($t);
    }
}
