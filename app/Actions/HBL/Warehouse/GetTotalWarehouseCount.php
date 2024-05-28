<?php

namespace App\Actions\HBL\Warehouse;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalWarehouseCount
{
    use AsAction;

    public function handle(): int
    {
        return HBL::warehouse()->count();
    }
}
