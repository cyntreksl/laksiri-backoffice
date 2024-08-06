<?php

namespace App\Interfaces\CallCenter;

use App\Models\HBL;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function createAndIssueToken(HBL $hbl);
}
