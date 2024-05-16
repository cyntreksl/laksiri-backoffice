<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalHBLCount
{
    use AsAction;

    public function handle(): int
    {
        return HBL::count();
    }
}
