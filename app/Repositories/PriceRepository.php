<?php

namespace App\Repositories;

use App\Actions\BranchPrice\CreatePriceRule;
use App\Actions\BranchPrice\DeletePriceRule;
use App\Actions\BranchPrice\GetPriceRules;
use App\Actions\BranchPrice\UpdatePriceRule;
use App\Interfaces\PriceRepositoryInterface;
use App\Models\BranchPrice;

class PriceRepository implements PriceRepositoryInterface
{
    public function getPriceRules()
    {
        return GetPriceRules::run();
    }

    public function createPriceRule(array $data)
    {
        return CreatePriceRule::run($data);
    }

    public function updatePriceRule(array $data, BranchPrice $branchPrice)
    {
        return UpdatePriceRule::run($data, $branchPrice);
    }

    public function deletePriceRule(BranchPrice $branchPrice)
    {
        return DeletePriceRule::run($branchPrice);
    }
}
