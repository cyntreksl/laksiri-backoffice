<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function apply(Builder $query, $value);
}
