<?php

namespace App\Interfaces;

use App\Models\Zone;

interface ZoneRepositoryInterface
{
    public function getZones();
    public function store(array $data);
    public function destroy(Zone $user);
}
