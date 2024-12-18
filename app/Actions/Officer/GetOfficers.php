<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOfficers
{
    use AsAction;

    public function handle(): Collection|array
    {
        return Officer::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)->get();
    }
}
