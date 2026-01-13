<?php

namespace App\Events;

use App\Models\Token;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tokenId;
    public $tokenNumber;
    public $hblNumber;
    public $cancelledBy;
    public $cancelledAt;
    public $cancellationReason;

    /**
     * Create a new event instance.
     */
    public function __construct(Token $token)
    {
        $this->tokenId = $token->id;
        $this->tokenNumber = $token->token;
        $this->hblNumber = $token->hbl?->hbl_number;
        $this->cancelledBy = $token->cancelledBy?->name;
        $this->cancelledAt = $token->cancelled_at?->toIso8601String();
        $this->cancellationReason = $token->cancellation_reason;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('package-queue');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'token.cancelled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'token_id' => $this->tokenId,
            'token_number' => $this->tokenNumber,
            'hbl_number' => $this->hblNumber,
            'cancelled_by' => $this->cancelledBy,
            'cancelled_at' => $this->cancelledAt,
            'cancellation_reason' => $this->cancellationReason,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
