<?php

namespace App\Factory\Warehouse\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class PaymentStatusFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $value = ! is_array($value) ? explode(',', $value) : $value;

        return $query->whereHas('hblPayment', function (Builder $query) use ($value) {
            $query->whereIn('status', $value);
        });
    }
}
