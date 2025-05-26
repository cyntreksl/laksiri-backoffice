<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLSystemStatus
{
    use AsAction;

    public function handle(HBL $HBL, float $status, ?string $message = null): HBL
    {
        $HBL->system_status = $status;
        $HBL->save();
        HBLSystemStatusLog::run($HBL, $status, $message);

        return $HBL;
    }
}
