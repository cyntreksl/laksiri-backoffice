<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! empty($value)) {
            return $query->where('status', $value);
        }

        return $query;
    }
}
