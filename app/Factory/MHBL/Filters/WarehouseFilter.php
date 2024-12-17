<?php

namespace App\Factory\MHBL\Filters;

use App\Actions\Branch\GetBranchByName;
use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class WarehouseFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        if (! is_null($value)) {
            $value = ! is_array($value) ? explode(',', $value) : $value;
            $ids = array_map(function ($item) {
                return GetBranchByName::run($item)->id;
            }, $value);

            return $query->whereIn('warehouse_id', $ids);
        }

        return $query;
    }
}
