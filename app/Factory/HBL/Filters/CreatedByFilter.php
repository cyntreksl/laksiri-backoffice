<?php

namespace App\Factory\HBL\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CreatedByFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if ($value) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereIn('created_by', $value);
        }
    }
}
