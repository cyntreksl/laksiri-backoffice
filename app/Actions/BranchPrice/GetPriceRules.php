<?php

namespace App\Actions\BranchPrice;

use App\Models\BranchPrice;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPriceRules
{
    use AsAction;

    public function handle(): Collection|array
    {
        return BranchPrice::all();
    }
}
