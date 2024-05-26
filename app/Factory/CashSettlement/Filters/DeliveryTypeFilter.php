<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DeliveryTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = ! is_array($value) ? explode(',', $value) : $value;

        return $query->whereIn('hbl_type', $value);
    }
}
