<?php

namespace App\Interfaces;

use App\Models\BranchPrice;

interface PriceRepositoryInterface
{
    public function getPriceRules();

    public function createPriceRule(array $data);

    public function updatePriceRule(array $data, BranchPrice $branchPrice);

    public function deletePriceRule(BranchPrice $branchPrice);
}
