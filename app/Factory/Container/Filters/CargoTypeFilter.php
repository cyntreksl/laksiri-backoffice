<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CargoTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereIn('cargo_type', $value);
        }

        return $query;
    }
}
