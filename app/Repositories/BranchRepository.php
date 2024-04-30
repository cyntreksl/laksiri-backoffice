<?php

namespace App\Repositories;

use App\Actions\Branch\GetBranches;
use App\Interfaces\BranchRepositoryInterface;

class BranchRepository implements BranchRepositoryInterface
{
    public function getBranches()
    {
        return GetBranches::run();
    }
}
