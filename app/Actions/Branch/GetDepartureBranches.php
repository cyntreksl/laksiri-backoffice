<?php

namespace App\Actions\Branch;

use App\Enum\BranchType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDepartureBranches
{
    use AsAction;

    public function handle(): Collection|array
    {
        return Branch::whereType(BranchType::DEPARTURE)->where('is_third_party_agent', false)->get();
    }
}
