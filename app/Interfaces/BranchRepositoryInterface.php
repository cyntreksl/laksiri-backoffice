<?php

namespace App\Interfaces;

use App\Models\Branch;

interface BranchRepositoryInterface
{
    public function getBranches();

    public function getDestinationBranches();

    public function createBranch(array $data);

    public function updateBranch(array $data, Branch $branch);

    public function getUserBranches();

    public function getBranchesByType();

    public function createAgent(array $data);

    public function updateAgent(array $data, Branch $branch);

    public function getDepartureBranches();
}
