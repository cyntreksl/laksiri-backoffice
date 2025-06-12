<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCourierStatus
{
    use AsAction;

    public function handle(Courier $courier, string $status): Courier
    {
        $oldStatus = $courier->status;

        // Update the courier status
        $courier->update(['status' => $status]);

        // Add status log for tracking
        $courier->addStatus("Status changed from '{$oldStatus}' to '{$status}' by ".auth()->user()->name);

        // Refresh the model to get updated data
        $courier->refresh();

        return $courier;
    }
}
