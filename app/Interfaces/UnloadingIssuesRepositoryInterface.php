<?php

namespace App\Interfaces;

use App\Models\HBL;

interface UnloadingIssuesRepositoryInterface
{
    public function getUnloadingIssuesByHbl(HBL $hbl);
}
