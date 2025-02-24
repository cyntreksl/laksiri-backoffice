<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAgent
{
    use AsAction;

    public function handle(): Collection|array
    {
        return Branch::where('is_third_party_agent', true)->get();
    }
}
