<?php

namespace App\Actions\PickUps;

use App\Actions\User\GetUserCurrentBranch;
use App\Models\PickUp;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePickUp
{
    use AsAction;

    public function handle(array $data): PickUp
    {
        return PickUp::create([
            'reference' => GeneratePickupReferenceNumber::run(GetUserCurrentBranch::run()['branchName']),
            'branch_id' => GetUserCurrentBranch::run()['branchId'],
            'cargo_type' => $data['cargo_type'],
            'name' => Str::title($data['name']),
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'address' => Str::title($data['address']),
            'location_name' => $data['location'],
            'zone_id' => $data['zone_id'],
            'notes' => Str::title($data['notes']),
            'pickup_date' => $data['pickup_date'],
            'pickup_time_start' => $data['pickup_time_start'],
            'pickup_time_end' => $data['pickup_time_end'],
            'is_from_important_customer' => (bool) $data['is_from_important_customer'],
            'is_urgent_pickup' => (bool) $data['is_urgent_pickup'],
            'pickup_type' => $data['pickup_type'],
            'pickup_note' => Str::title($data['pickup_note']),
            'created_by' => auth()->id(),
        ]);
    }
}
