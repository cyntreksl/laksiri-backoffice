<?php

namespace App\Interfaces;

interface PickupRepositoryInterface
{
    public function getPickups();

    public function storePickup(array $data);

    public function getNoteTypes();
}
