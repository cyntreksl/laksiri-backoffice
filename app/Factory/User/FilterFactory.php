<?php

namespace App\Factory\User;

use App\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FilterFactory
{
    public static function apply(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            $filter = self::create($key);
            if ($filter instanceof FilterInterface && $value != 'false') {
                $filter->apply($query, $value);
            }
        }
    }

    public static function create(string $filterName): ?FilterInterface
    {
        $className = 'App\\Factory\\User\\Filters\\'.ucfirst(Str::camel($filterName)).'Filter';
        if (class_exists($className)) {
            return new $className;
        }

        return null;
    }
}
