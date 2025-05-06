<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class ApproveContainerPayments
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
                    'is_finance_approved' => true,
                    'finance_approved_by' => Auth::user()->id,
                    'finance_approved_date' => now(),
                ]
            );
    }
}
