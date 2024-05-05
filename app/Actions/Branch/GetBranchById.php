<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetBranchById
{
    use AsAction;

    public function handle(int $id): Branch
    {
        return Branch::findOrFail($id);
    }
}
