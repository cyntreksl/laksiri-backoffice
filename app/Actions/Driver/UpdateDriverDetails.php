<?php

namespace App\Actions\Driver;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDriverDetails
{
    use AsAction;

    public function handle(array $data, User $user): User
    {

        // update driver information
        $user->update([
            'name' => $data['name'],
            'contact' => $data['contact'],
            'working_hours_start' => $data['working_hours_start'],
            'working_hours_end' => $data['working_hours_end'],
            'preferred_zone' => implode(',', $data['preferred_zone']),
        ]);

        return $user;
    }
}
