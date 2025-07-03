<?php

namespace App\Actions\HBL\Payments;

use App\Models\Payment;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\DB;

class CreateHBLPayment
{
    use AsAction;

    /**
     * Handle the creation of a payment record for an HBL.
     *
     * @param array $data
     * @return Payment
     * @throws \Throwable
     */
    public function handle(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Payment::create([
                'hbl_id'              => $data['hbl_id'],
                'paid_amount'         => $data['paid_amount'],
                'total_amount'        => $data['total_amount'],
                'due_amount'          => $data['due_amount'],
                'payment_method'      => $data['payment_method'],
                'paid_by'             => $data['paid_by'] ?? null,
                'paid_at'             => $data['paid_at'] ?? now(),
                'notes'               => $data['notes'] ?? null,
                'is_cancelled'        => false,
                'cancelled_at'        => null,
                'cancellation_reason' => null,
            ]);
        });
    }
}
