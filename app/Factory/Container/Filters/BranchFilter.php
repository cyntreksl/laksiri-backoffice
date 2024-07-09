<?php

namespace App\Factory\Container\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class BranchFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! empty($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;
            $query->whereIn('branch_id', $value);
        }

        return $query;
    }
}
