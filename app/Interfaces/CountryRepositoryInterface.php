<?php

namespace App\Interfaces;

interface CountryRepositoryInterface
{
    public function getAllPhoneCodes();

    public function getAllCountries();
}
