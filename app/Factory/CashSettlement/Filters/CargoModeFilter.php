<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CargoModeFilter implements FilterInterface
{

    public function apply(Builder $query, $value)
    {
        return $query->whereIn('cargo_type', explode(',', $value));
    }
}
