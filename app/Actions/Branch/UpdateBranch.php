<?php

namespace App\Actions\Branch;

use App\Actions\User\SwitchUserBranch;
use App\Models\Branch;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateBranch
{
    use AsAction;

    public function handle(array $data, Branch $branch)
    {
        $updated = $branch->update([
            'name' => $data['name'],
            'branch_code' => $data['branch_code'],
            'slug' => Str::slug($data['name']),
            'type' => $data['type'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
            'country_code' => $data['country_code'],
            'country' => $data['country'],
            'timezone' => $data['timezone'],
            'email' => $data['email'],
            'container_delays' => $data['container_delays'],
            'maximum_demurrage_discount' => $data['maximum_demurrage_discount'],
            'cargo_modes' => json_encode($data['cargo_modes']),
            'delivery_types' => json_encode($data['delivery_types']),
            'package_types' => json_encode($data['package_types']),
        ]);

        if (session('current_branch_id') === $branch->id) {
            SwitchUserBranch::run($branch);
        }

        return $updated;
    }
}
