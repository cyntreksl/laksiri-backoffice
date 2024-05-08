<?php

namespace App\Interfaces;

interface ZoneRepositoryInterface
{
    public function getZones();
    public function store(array $data);
}
