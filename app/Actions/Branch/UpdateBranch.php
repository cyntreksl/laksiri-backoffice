<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateBranch
{
    use AsAction;

    public function handle(array $data, Branch $branch)
    {
        return $branch->update([
            'name' => $data['name'],
            'branch_code' => $data['branch_code'],
            'slug' => Str::slug($data['name']),
            'type' => $data['type'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
            'country_code' => $data['country_code'],
            'email' => $data['email'],
            'container_delays' => $data['container_delays'],
            'cargo_modes' => json_encode($data['cargo_modes']),
            'delivery_types' => json_encode($data['delivery_types']),
            'package_types' => json_encode($data['package_types']),
        ]);
    }
}
