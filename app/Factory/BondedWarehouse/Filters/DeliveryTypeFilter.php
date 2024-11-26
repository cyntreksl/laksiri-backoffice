<?php

namespace App\Factory\BondedWarehouse\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DeliveryTypeFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereIn('hbl_type', $value);
        }
    }
}
