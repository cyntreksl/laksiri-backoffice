<?php

namespace App\Factory\HBL\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ShipmentFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! empty($value)) {
            return $query->where(function ($q) use ($value) {
                $q->whereHas('packages.containers', function ($containerQuery) use ($value) {
                    $containerQuery->where('containers.id', $value);
                })->orWhereHas('packages.duplicate_containers', function ($containerQuery) use ($value) {
                    $containerQuery->where('containers.id', $value);
                });
            });
        }

        return $query;
    }
}
