<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Lorisleiva\Actions\Concerns\AsAction;

class RevokeContainerPaymentApprovals
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
                    'is_finance_approved' => false,
                    'finance_approved_by' => null,
                    'finance_approved_date' => null,
                ]
            );
    }
}
