<?php

namespace App\Repositories;

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\GetBranches;
use App\Actions\Branch\GetDestinationBranches;
use App\Actions\Branch\GetUserBranches;
use App\Actions\Branch\UpdateBranch;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;

class BranchRepository implements BranchRepositoryInterface
{
    public function getBranches()
    {
        return GetBranches::run();
    }

    public function getDestinationBranches()
    {
        return GetDestinationBranches::run();
    }

    public function createBranch(array $data)
    {
        return CreateBranch::run($data);
    }

    public function updateBranch(array $data, Branch $branch)
    {
        return UpdateBranch::run($data, $branch);
    }

    public function getUserBranches()
    {
        return GetUserBranches::run();
    }
}
