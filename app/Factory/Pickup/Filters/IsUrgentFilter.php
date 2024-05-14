<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class IsUrgentFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = (bool)$value;

        return $query->where('is_urgent_pickup', $value);
    }
}
