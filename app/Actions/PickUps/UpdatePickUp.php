<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePickUp
{
    use AsAction;

    public function handle(array $data, PickUp $pickup)
    {
        $data['notes'] = is_array($data['notes'])
            ? Str::title(implode(', ', $data['notes']))
            : Str::title($data['notes']);
        $data['package_types'] = json_encode($data['note_type']);
        $pickup->update($data);
    }
}
