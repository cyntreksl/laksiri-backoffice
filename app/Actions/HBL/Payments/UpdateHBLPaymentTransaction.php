<?php

namespace App\Actions\HBL\Payments;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPaymentTransaction
{
    use AsAction;

    public function handle(HBL $hbl, array $data)
    {
        $payment = $hbl->payments()->first();
        if ($payment) {
            $payment->update($data);
        } else {
            $hbl->payments()->create($data);
        }
    }
}
