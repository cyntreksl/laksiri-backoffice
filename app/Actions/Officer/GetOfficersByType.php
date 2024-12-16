<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Database\Eloquent\Collection;

class GetOfficersByType
{
    use AsAction;

    public function handle($type): Collection|array
    {
        return Officer::withoutGlobalScopes()->where('type', $type)->get();
    }
}
