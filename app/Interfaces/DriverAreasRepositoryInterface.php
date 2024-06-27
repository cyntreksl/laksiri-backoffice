<?php

namespace App\Interfaces;

use App\Models\Area;

interface DriverAreasRepositoryInterface
{
    public function getAreas();

    public function getDriverAreas($id);

    public function createDriverAreas(array $data);

    public function editDriverAreas(array $data);

    public function destroy(Area $DriverAreas);
}
