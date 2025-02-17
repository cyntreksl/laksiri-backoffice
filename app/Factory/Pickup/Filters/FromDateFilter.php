<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FromDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $dateValue = Carbon::parse($value); // Ensure $value is parsed into a date

        return $query->whereDate('pickup_date', '>=', $dateValue);
    }
}
