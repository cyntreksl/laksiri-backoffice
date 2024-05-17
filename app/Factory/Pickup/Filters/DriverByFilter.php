<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DriverByFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        return $query->where('driver_id', $value);
    }
}
