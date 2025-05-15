<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CompleteContainerPayments
{
    use AsAction;

    /**
     * Update the SL Rate for given currency IDs.
     *
     * @return int Number of records updated
     */
    public function handle(array $data): int
    {
        return ContainerPayment::whereIn('id', $data['paymentsIds'])
            ->update(
                [
                    'is_paid' => true,
                    'paid_by' => Auth::user()->id,
                    'payment_received_by' => $data['payment_received_by'],
                    'paid_date' => now(),
                ]
            );
    }
}
