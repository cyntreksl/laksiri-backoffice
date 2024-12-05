<?php

namespace App\Repositories;

use App\Actions\Country\GetAllPhoneCodes;
use App\Interfaces\CountryRepositoryInterface;

class CountryRepository implements CountryRepositoryInterface
{
    public function getAllPhoneCodes()
    {
        return GetAllPhoneCodes::run();
    }
}
