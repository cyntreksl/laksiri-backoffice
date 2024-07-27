<?php

namespace App\Actions\Driver\DriverLocation;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDriverLocation
{
    use AsAction;

    public function handle(User $user, array $data): void
    {
        $user->driverLocation()->create([
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'meta_data' => $data['meta_data'] ? json_encode($data['meta_data']) : null,
        ]);
    }
}
