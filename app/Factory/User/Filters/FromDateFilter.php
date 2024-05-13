<?php

namespace App\Factory\User\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class FromDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        return $query->where('created_at', '>=', $value);
    }
}
