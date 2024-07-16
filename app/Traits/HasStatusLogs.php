<?php

namespace App\Traits;

use App\Models\StatusLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasStatusLogs
{
    public function addStatus($status, $description = null): void
    {
        $this->statusLogs()->create([
            'status' => $status,
            'user_id' => auth()->id(),
            'description' => $description,
        ]);
    }

    public function statusLogs(): MorphMany
    {
        return $this->morphMany(StatusLog::class, 'statusable');
    }
}
