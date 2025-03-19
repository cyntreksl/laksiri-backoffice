<?php

namespace App\Factory\Pickup\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PickupDateFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $dateValue = Carbon::parse($value);

        return $query->whereDate('pickup_date', '>=', $dateValue);
    }
}
