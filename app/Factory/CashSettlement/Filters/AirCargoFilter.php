<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class AirCargoFilter implements FilterInterface
{

    public function apply(Builder $query, $value)
    {
        return $query->orWhere('cargo_type', '=', 'Air Cargo');
    }
}
