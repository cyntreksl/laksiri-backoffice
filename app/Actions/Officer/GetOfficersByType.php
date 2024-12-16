<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOfficersByType
{
    use AsAction;

    public function handle($type): Collection|array
    {
        return Officer::withoutGlobalScopes()->where('type', $type)->get();
    }
}
