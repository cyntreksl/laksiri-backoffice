<?php

namespace App\Factory\Courier\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ToDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        return $query->whereDate('created_at', '<=', $value);
    }
}
