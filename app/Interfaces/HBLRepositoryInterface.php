<?php

namespace App\Interfaces;

use App\Models\HBL;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function storeHBL(array $data);

    public function updateHBL(array $data, HBL $HBL);

    public function deleteHBL(HBL $HBL);

    public function getHBLsWithPackages();

    public function toggleHold(HBL $hbl);
}
