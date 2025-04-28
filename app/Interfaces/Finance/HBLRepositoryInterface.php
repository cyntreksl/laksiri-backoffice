<?php

namespace App\Interfaces\Finance;

interface HBLRepositoryInterface
{
    public function getHBLsWithPackages();

    public function getApproveHBLs();

    public function financeApproved(array $hblIds);

    public function removeFinanceApproval(array $hblIds);
}
