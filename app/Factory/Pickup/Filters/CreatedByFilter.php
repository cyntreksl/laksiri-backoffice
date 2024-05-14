<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CreatedByFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = is_array($value) ? array_values($value) : $value;

        return $query->orWhereIn('created_by', [$value]);
    }
}
