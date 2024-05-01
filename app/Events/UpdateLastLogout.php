<?php

namespace App\Events;

use App\Enum\UserStatus;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateLastLogout
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(Logout $event): void
    {
        try {
            $user = $event->user;
            $user->last_logout_at = Carbon::now();
            $user->status = UserStatus::DEACTIVATE->value;
            $user->save();
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }
}
