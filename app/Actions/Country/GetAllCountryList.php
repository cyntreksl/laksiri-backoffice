<?php

namespace App\Actions\Country;

use App\Models\Country;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllCountryList
{
    use AsAction;

    public function handle()
    {
        return Country::getAllCountryNames();
    }
}
