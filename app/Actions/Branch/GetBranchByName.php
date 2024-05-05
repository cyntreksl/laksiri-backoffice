<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetBranchByName
{
    use AsAction;

    public function handle($name): Branch
    {
        return Branch::whereName($name)->firstOrFail();
    }
}
