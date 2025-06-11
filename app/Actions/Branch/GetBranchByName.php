<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Lorisleiva\Actions\Concerns\AsAction;

class GetBranchByName
{
    use AsAction;

    public function handle($name): Branch
    {
        return Branch::where('name', 'LIKE', $name)->firstOrFail();
    }
}
