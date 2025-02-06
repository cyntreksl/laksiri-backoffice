<?php

namespace App\Factory\CashSettlement\Filters;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class PaymentStatusFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;

            return $query->whereHas('hblPayment', function (Builder $query) use ($value) {
                $query->whereIn('status', $value)
                    ->whereRaw('`id` = (SELECT MAX(`id`) FROM `hbl_payments` WHERE `hbl_payments`.`hbl_id` = `hbl`.`id`)');
            });
        }

        return $query;
    }
}
