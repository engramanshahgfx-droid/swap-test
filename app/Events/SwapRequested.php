<?php

namespace App\Events;

use App\Models\SwapRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwapRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $swapRequest;

    public function __construct(SwapRequest $swapRequest)
    {
        $this->swapRequest = $swapRequest;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->swapRequest->to_user_id),
            new PrivateChannel('managers'),
        ];
    }

    public function broadcastAs()
    {
        return 'swap.requested';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->swapRequest->id,
            'from_user' => [
                'id' => $this->swapRequest->fromUser->id,
                'name' => $this->swapRequest->fromUser->full_name,
            ],
            'flight' => [
                'number' => $this->swapRequest->publishedTrip->flight->flight_number,
                'route' => $this->swapRequest->publishedTrip->flight->departure_airport . ' → ' . 
                          $this->swapRequest->publishedTrip->flight->arrival_airport,
            ],
            'created_at' => $this->swapRequest->created_at,
        ];
    }
}
