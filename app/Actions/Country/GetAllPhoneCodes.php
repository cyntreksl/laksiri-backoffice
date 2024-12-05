<?php

namespace App\Actions\Country;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Container;
use App\Models\Country;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllPhoneCodes
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle()
    {
        return Country::getAllPhoneCodes();
    }
}
