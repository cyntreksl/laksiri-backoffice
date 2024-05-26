<?php

namespace App\Actions\HBL\CashSettlement;

use App\Models\HBL;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCashSettlementByIds
{
    use AsAction;

    public function handle(array $value): Collection
    {
        return HBL::cashSettlement()->whereIn('id', $value)->get();
    }
}
