<?php

namespace App\Repositories;

use App\Actions\NoteType\GetNoteTypes;
use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\GetPickups;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;

class PickupRepository implements PickupRepositoryInterface
{
    public function getPickups()
    {
        return GetPickups::run();
    }

    public function storePickup(array $data)
    {
        // assign agent

        // assign location longitude, latitude and name

        // store pickup
        return CreatePickUp::run($data);
    }

    public function getNoteTypes()
    {
        return GetNoteTypes::run();
    }
}
