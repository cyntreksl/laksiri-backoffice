<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\HBL;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function storeHBL(array $data);

    public function updateHBL(array $data, HBL $HBL);

    public function deleteHBL(HBL $HBL);

    public function getHBLsWithPackages();

    public function toggleHold(HBL $hbl);

    public function getUnloadedHBLsByCargoType(string $cargoType);

    public function getLoadedHBLsByCargoType(Container $container, string $cargoType);

    public function getHBLWithUnloadedPackagesByReference(string $reference, string $cargo_type);
}
