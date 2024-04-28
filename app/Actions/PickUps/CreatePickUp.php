<?php

namespace App\Actions\PickUps;

use App\Enum\CargoType;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePickUp
{
    use AsAction;

    public function handle(array $data): PickUp
    {
        return PickUp::create([
            'reference' => GeneratePickupReferenceNumber::run(),
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
}
