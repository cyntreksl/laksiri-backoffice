<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ZoneByFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = is_array($value) ? array_values($value) : $value;

        return $query->orWhereIn('zone_id', [$value]);
    }
}
