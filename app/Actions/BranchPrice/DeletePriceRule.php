<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePriceRule
{
    use AsAction;

    public function handle(BranchPrice $branchPrice)
    {
        $branchPrice->delete();
    }
}
