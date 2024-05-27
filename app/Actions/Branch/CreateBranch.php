<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateBranch
{
    use AsAction;

    public function handle(array $data): Branch
    {
        return Branch::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'type' => $data['type'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
            'cargo_modes' => json_encode($data['cargo_modes']),
            'delivery_types' => json_encode($data['delivery_types']),
            'package_types' => json_encode($data['package_types']),
        ]);
    }
}
