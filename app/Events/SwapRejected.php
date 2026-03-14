<?php

namespace App\Events;

use App\Models\SwapRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwapRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $swapRequest;
    public $rejectedBy;
    public $reason;

    public function __construct(SwapRequest $swapRequest, string $rejectedBy, ?string $reason = null)
    {
        $this->swapRequest = $swapRequest;
        $this->rejectedBy = $rejectedBy;
        $this->reason = $reason;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->swapRequest->from_user_id),
        ];
    }

    public function broadcastAs()
    {
        return 'swap.rejected';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->swapRequest->id,
            'rejected_by' => $this->rejectedBy,
            'reason' => $this->reason,
            'flight' => [
                'number' => $this->swapRequest->publishedTrip->flight->flight_number,
            ],
        ];
    }
}
