<?php

namespace App\Interfaces;

use App\Models\Container;

interface ContainerRepositoryInterface
{
    public function store(array $data) :Container;
}
