<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ContainerTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! empty($value)) {
            return $query->where('container_type', $value);
        }

        return $query;
    }
}
