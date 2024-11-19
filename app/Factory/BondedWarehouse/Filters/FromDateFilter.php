<?php

namespace App\Factory\BondedWarehouse\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class FromDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if ($value) {
            $query->whereDate('created_at', '>=', $value);
        }

        return $query;
    }
}
