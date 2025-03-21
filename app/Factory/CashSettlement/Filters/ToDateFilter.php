<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ToDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if ($value) {
            return $query->whereDate('created_at', '<=', $value);
        }

        return $query;
    }
}
