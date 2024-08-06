<?php

namespace App\Factory\HBL\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CargoModeFilter implements FilterInterface
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
