<?php

namespace App\Actions\CallFlag;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCallFlag
{
    use AsAction;

    public function handle(HBL $hbl, array $data)
    {
        $hbl->callFlags()->create($data);
    }
}
