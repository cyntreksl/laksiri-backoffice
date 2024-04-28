<?php

namespace App\Repositories;

use App\Enum\CargoType;
use App\Interfaces\PickupRepositoryInterface;
use App\Models\PickUp;

class PickupRepository implements PickupRepositoryInterface
{
    public function storePickup(array $data)
    {
        $reference_number = $this->generatePickupReferenceNumber();
        // assign agent

        // assign location longitude, latitude and name

        // store pickup
        PickUp::create([
            'reference' => $reference_number,
            'agent_id' => 1,
            'cargo_type' => $data['cargo_type'] === 'sea' ? CargoType::SEA_CARGO : CargoType::AIR_CARGO,
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'address' => $data['address'],
            'location_name' => $data['location'],
            'zone_id' => $data['zone_id'],
            'notes' => $data['note'],
            'created_by' => auth()->id(),
        ]);
    }

    // generate reference number using REF111111 pattern.
    private function generatePickupReferenceNumber(): string
    {
        return 'REF' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
