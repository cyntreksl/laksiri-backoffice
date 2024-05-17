<?php

namespace App\Interfaces;

use App\Models\HBL;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function storeHBL(array $data);

    public function deleteHBL(HBL $HBL);
}
