<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ContainerTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = is_array($value) ? $value : array_map('trim', explode(',', $value));

        return $query->whereIn('container_type', $value);
    }
}
