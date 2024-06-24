<?php

namespace App\Repositories;

use App\Actions\DriverAreas\CreateDriverAreas;
use App\Actions\DriverAreas\DeleteDriverAreas;
use App\Actions\DriverAreas\GetAllDriverAreas;
use App\Actions\DriverAreas\GetDriverAreas;
use App\Actions\DriverAreas\UpdateDriverAreas;
use App\Interfaces\DriverAreasRepositoryInterface;
use App\Models\Area;

class DriverAreasRepository implements DriverAreasRepositoryInterface
{
    public function getAreas()
    {
        return GetAllDriverAreas::run();
    }

    public function getDriverAreas($id)
    {
        return GetDriverAreas::run($id);
    }

    public function createDriverAreas(array $data)
    {
        return CreateDriverAreas::run($data);
    }

    public function editDriverAreas(array $data)
    {
        return UpdateDriverAreas::run($data);
    }

    public function destroy(Area $DriverAreas): void
    {
        DeleteDriverAreas::run($DriverAreas);
    }
}
