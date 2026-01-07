<?php

namespace App\Events;

use App\Models\Container;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageUnloaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $containerId;
    public $package;
    public $action; // 'unload' or 'reload'
    public $userId;
    public $userName;

    /**
     * Create a new event instance.
     */
    public function __construct(int $containerId, array $package, string $action, int $userId, string $userName)
    {
        $this->containerId = $containerId;
        $this->package = $package;
        $this->action = $action;
        $this->userId = $userId;
        $this->userName = $userName;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('container.'.$this->containerId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'package.'.$this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'container_id' => $this->containerId,
            'package' => $this->package,
            'action' => $this->action,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

