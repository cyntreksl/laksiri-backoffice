<?php

namespace App\Interfaces;

interface BranchRepositoryInterface
{
    public function getBranches();

    public function createBranch(array $data);
}
