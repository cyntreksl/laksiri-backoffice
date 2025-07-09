<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePickup
{
    use AsAction;

    public function handle(PickUp $pickup, ?string $deleteRemarks = null, ?string $deleteMainReason = null): void
    {
        if ($deleteRemarks !== null || $deleteMainReason !== null) {
            $pickup->delete_remarks = $deleteRemarks;
            $pickup->delete_main_reason = $deleteMainReason;
            $pickup->save();
        }
        $pickup->delete();
    }
}
