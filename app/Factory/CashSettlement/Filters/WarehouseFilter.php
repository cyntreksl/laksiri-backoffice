<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class WarehouseFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereIn('warehouse', $value);
        }

        return $query;
    }
}
