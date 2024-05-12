<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DeliveryTypeFilter implements FilterInterface
{

    public function apply(Builder $query, $value)
    {
        return $query->whereIn('hbl_type', explode(',', $value));
    }
}
