<?php

namespace App\Actions\Delivery;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class ReleaseHBLDelivery
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(HBL $hbl, array $data): HBL
    {
        if (count($hbl->packages) === count($data['released_packages'])) {
            $hbl->is_released = true;
            $hbl->save();
        }

        return $hbl;
    }
}
