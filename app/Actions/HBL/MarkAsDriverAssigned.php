<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDriverAssigned
{
    use AsAction;

    public function handle(array $hbl_ids): Collection
    {
        $hbls = HBL::whereIn('id', $hbl_ids)->get();

        HBL::whereIn('id', $hbl_ids)->update(['is_driver_assigned' => true]);

        return $hbls->map(function ($hbl) {
            $hbl->is_driver_assigned = true;
            return $hbl;
        });
    }
}
