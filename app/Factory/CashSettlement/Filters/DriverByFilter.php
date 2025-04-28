<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class DriverByFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if ($value) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereHas('pickup', function (Builder $query) use ($value) {
                $query->whereIn('driver_id', $value);
            });
        }
    }
}
