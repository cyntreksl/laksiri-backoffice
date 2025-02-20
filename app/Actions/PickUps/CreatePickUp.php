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
        dd($data);
        $pickup_note = isset($data['pickup_note']) ? Str::title($data['pickup_note']) : null;

        $pickup = PickUp::create([
            'reference' => GeneratePickupReferenceNumber::run(GetUserCurrentBranch::run()['branchName']),
            'branch_id' => GetUserCurrentBranch::run()['branchId'],
            'cargo_type' => $data['cargo_type'],
            'name' => Str::title($data['name']),
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'address' => Str::title($data['address']),
            'location_name' => $data['location'] ?? null,
            'zone_id' => $data['zone_id'] ?? null,
            'notes' => json_encode($data['notes']),
            'pickup_date' => $data['pickup_date'],
            'pickup_time_start' => $data['pickup_time_start'],
            'pickup_time_end' => $data['pickup_time_end'],
            'pickup_type' => $data['pickup_type'] ?? null,
            'pickup_note' => $pickup_note,
            'created_by' => auth()->id(),
            'system_status' => PickUp::SYSTEM_STATUS_PICKUP_CREATED,
        ]);

        $pickup->addStatus('Pickup Created');

        return $pickup;
    }
}
