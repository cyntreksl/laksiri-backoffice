<?php

namespace App\Actions\PickUps;

use App\Actions\User\GetUserCurrentBranch;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePickUp
{
    use AsAction;

    public function handle(array $data): PickUp
    {
        return PickUp::create([
            'reference' => GeneratePickupReferenceNumber::run(),
            'branch_id' => GetUserCurrentBranch::run()['branchId'],
            'cargo_type' => $data['cargo_type'],
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'address' => $data['address'],
            'location_name' => $data['location'],
            'zone_id' => $data['zone_id'],
            'notes' => $data['notes'],
            'pickup_date' => $data['pickup_date'],
            'pickup_time_start' => $data['pickup_time_start'],
            'pickup_time_end' => $data['pickup_time_end'],
            'is_from_important_customer' => $data['is_from_important_customer'],
            'is_urgent_pickup'=> $data['is_urgent_pickup'],
            'created_by' => auth()->id(),
        ]);
    }
}
