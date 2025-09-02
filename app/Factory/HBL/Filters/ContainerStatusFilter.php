<?php

namespace App\Factory\HBL\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ContainerStatusFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! empty($value)) {
            return $query->whereHas('containers', function ($containerQuery) use ($value) {
                $containerQuery->where('containers.status', $value);
            });
        }

        return $query;
    }
}
