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
        $pickup_note = isset($data['pickup_note']) ? Str::title($data['pickup_note']) : null;

        if (! empty($data['notes'])) {
            $data['notes'] = is_array($data['notes'])
                ? Str::title(implode(', ', $data['notes']))
                : Str::title($data['notes']);
        }

        $package_types = null;
        if (! empty($data['package_types'])) {
            $package_types = isset($data['package_types']) ? json_encode($data['package_types']) : json_encode($data['note_type']);
        }

        $pickup = PickUp::create([
            'reference' => GeneratePickupReferenceNumber::run(GetUserCurrentBranch::run()['branchName']),
            'branch_id' => GetUserCurrentBranch::run()['branchId'],
            'cargo_type' => $data['cargo_type'],
            'name' => Str::title($data['name']),
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'additional_mobile_number' => $data['additional_mobile_number'],
            'whatsapp_number' => ! empty($data['whatsapp_number']) ? $data['whatsapp_number'] : null,
            'address' => Str::title($data['address']),
            'location_name' => $data['location'] ?? null,
            'zone_id' => $data['zone_id'] ?? null,
            'notes' => ! empty($data['notes']) ? Str::title($data['notes']) : null,
            'package_types' => $package_types,
            'pickup_date' => ! empty($data['pickup_date']) ? $data['pickup_date'] : null,
            'pickup_time_start' => ! empty($data['pickup_time_start']) ? $data['pickup_time_start'] : null,
            'pickup_time_end' => ! empty($data['pickup_time_end']) ? $data['pickup_time_end'] : null,
            'pickup_type' => $data['pickup_type'] ?? null,
            'pickup_note' => $pickup_note,
            'created_by' => auth()->id(),
            'system_status' => PickUp::SYSTEM_STATUS_PICKUP_CREATED,
        ]);

        $pickup->addStatus('Pickup Created');

        return $pickup;
    }
}
