<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class IsHoldFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if ($value) {
            $value = (bool) $value;

            return $query->where('is_hold', '=', $value);
        }
    }
}
