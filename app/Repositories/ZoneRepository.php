<?php

namespace App\Repositories;

use App\Actions\HBL\GetHBLs;
use App\Actions\Zone\CreateZone;
use App\Actions\Zone\CreateZoneArea;
use App\Actions\Zone\DeleteZone;
use App\Actions\Zone\GetZones;
use App\Interfaces\ZoneRepositoryInterface;
use App\Models\Zone;

class ZoneRepository implements ZoneRepositoryInterface
{

    public function getZones()
    {
        return GetZones::run();
    }
    public function store(array $data)
    {
        $zone = CreateZone::run($data);
        CreateZoneArea::run($data['areas'], $zone);
        $zone->load('areas');

        return $zone;
    }
    public function destroy(Zone $user): void
    {
        DeleteZone::run($user);
    }
}

