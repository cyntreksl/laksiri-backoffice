<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Traits\HandlesDeadlocks;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLSystemStatus
{
    use AsAction, HandlesDeadlocks;

    public function handle(HBL $HBL, float $status, ?string $message = null): HBL
    {
        return $this->executeWithDeadlockRetry(function () use ($HBL, $status, $message) {
            $HBL->system_status = $status;
            $HBL->save();
            HBLSystemStatusLog::run($HBL, $status, $message);

            return $HBL;
        }, 3, 50); // 3 retries with 50ms base delay for this critical operation
    }
}
