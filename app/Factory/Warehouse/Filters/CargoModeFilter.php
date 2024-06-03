<?php

namespace App\Factory\Warehouse\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CargoModeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = ! is_array($value) ? explode(',', $value) : $value;

        return $query->whereIn('cargo_type', $value);
    }
}
