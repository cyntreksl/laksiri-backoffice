<?php

namespace App\Factory\HBL\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class HblTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            return $query->whereRaw('LOWER(hbl_type) = ?', [strtolower($value)]);
        }

        return $query;
    }
}
