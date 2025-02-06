<?php

namespace App\Repositories;

use App\Actions\Country\GetAllCountryList;
use App\Actions\Country\GetAllPhoneCodes;
use App\Interfaces\CountryRepositoryInterface;

class CountryRepository implements CountryRepositoryInterface
{
    public function getAllPhoneCodes()
    {
        return GetAllPhoneCodes::run();
    }

    public function getAllCountries()
    {
        return GetAllCountryList::run();
    }
}
