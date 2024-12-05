<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\HBL;

interface CountryRepositoryInterface
{
    public function getAllPhoneCodes();
}
