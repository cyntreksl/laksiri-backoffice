<?php

namespace App\Actions\Setting;

use App\Models\Setting;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSettings
{
    use AsAction;

    public function handle()
    {
        return Setting::first();
    }
}
