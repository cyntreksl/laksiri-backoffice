<?php

namespace App\Actions\Queue;

use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

class QueueCount
{
    use AsAction;

    public function handle(Builder $queue)
    {
        $queueTotal = $queue->count();
        $queueCompletedTotal = $queue->whereNotNull('left_at')->count();
        $pending = $pending = $queueTotal - $queueCompletedTotal;

        return response()->json([
            'total' => $queueTotal,
            'completed' => $queueCompletedTotal,
            'pending' => $pending,
        ], 200);
    }
}
