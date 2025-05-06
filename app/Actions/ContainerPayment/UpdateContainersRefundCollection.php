<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContainersRefundCollection
{
    use AsAction;

    /**
     * Update the SL Rate for given currency IDs.
     *
     * @return int Number of records updated
     */
    public function handle(array $currencyIds): int
    {
        return ContainerPayment::whereIn('id', $currencyIds)
            ->update(
                [
                    'is_refund_collected' => true,
                    'refund_collected_by' => Auth::user()->id,
                    'refund_collected_date' => now(),
                ]
            );
    }
}
