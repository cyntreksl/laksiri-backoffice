<?php

namespace App\Traits;

use App\Models\QueueLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasQueueLogs
{
    public function addQueueStatus(
        string $queue_type,
        int|string $customer_id,
        int|string $token_id,
        ?string $arrival_at,
        ?string $left_at,
    ): void {
        // Start with the base update array
        $updateData = [
            'auth_id' => auth()->id(),
        ];

        // Conditionally add arrival_at and left_at if they are not null
        if (! is_null($arrival_at)) {
            $updateData['arrival_at'] = $arrival_at;
        }

        if (! is_null($left_at)) {
            $updateData['left_at'] = $left_at;
        }

        // Perform the updateOrCreate operation
        $this->queueLogs()->updateOrCreate([
            'queue_type' => $queue_type,
            'customer_id' => $customer_id,
            'token_id' => $token_id,
        ], $updateData);
    }

    public function queueLogs(): MorphMany
    {
        return $this->morphMany(QueueLog::class, 'queueable');
    }
}
