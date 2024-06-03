<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ContainerTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = ! is_array($value) ? explode(',', $value) : $value;

        if (! empty($query->value('container_type'))) {
            return $query->whereIn('container_type', $value);
        }

        return $query;
    }
}
