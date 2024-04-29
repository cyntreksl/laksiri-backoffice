<?php

namespace App\Repositories;

use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\GetHBLs;
use App\Interfaces\HBLRepositoryInterface;

class HBLRepository implements HBLRepositoryInterface
{
    public function getHBLs()
    {
        return GetHBLs::run();
    }

    public function storeHBL(array $data)
    {
        return CreateHBL::run($data);
    }
}
