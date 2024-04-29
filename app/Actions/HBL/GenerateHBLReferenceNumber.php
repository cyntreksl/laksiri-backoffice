<?php

namespace App\Actions\HBL;

use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLReferenceNumber
{
    use AsAction;

    public function handle(): string
    {
        return 'REF'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
