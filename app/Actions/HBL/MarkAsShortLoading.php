<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsShortLoading
{
    use AsAction;

    public function handle(HBL $hbl): HBL
    {
        $hbl->update([
            'is_short_loading' => true,
        ]);

        return $hbl;
    }
}
