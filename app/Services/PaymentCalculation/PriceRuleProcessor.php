<?php

namespace App\Services\PaymentCalculation;

use Illuminate\Database\Eloquent\Collection;

class PriceRuleProcessor
{
    public function getLatestPriceRules(Collection $priceRules): Collection
    {
        $groupedPriceRules = $priceRules->groupBy('condition');

        return $groupedPriceRules->map(function (Collection $group) {
            return $group->sortByDesc('updated_at')->first();
        });
    }

    public function getSortedOperations(Collection $latestPriceRules): array
    {
        $operations = array_keys($latestPriceRules->toArray());

        usort($operations, function ($a, $b) {
            $numA = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
            $numB = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);

            return $numB <=> $numA;
        });

        return $operations;
    }
}
