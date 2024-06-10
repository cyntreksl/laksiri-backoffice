<?php

namespace App\Actions\Branch;

use App\Enum\BranchType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDestinationBranches
{
    use AsAction;

    public function handle(): Collection|array
    {
        return Branch::whereType(BranchType::DESTINATION)->get();
    }
}
