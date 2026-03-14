<?php

namespace App\Events;

use App\Models\SwapRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwapApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $swapRequest;
    public $approvedBy;

    public function __construct(SwapRequest $swapRequest, string $approvedBy)
    {
        $this->swapRequest = $swapRequest;
        $this->approvedBy = $approvedBy;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->swapRequest->from_user_id),
            new PrivateChannel('user.' . $this->swapRequest->to_user_id),
        ];
    }

    public function broadcastAs()
    {
        return 'swap.approved';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->swapRequest->id,
            'approved_by' => $this->approvedBy,
            'flight' => [
                'number' => $this->swapRequest->publishedTrip->flight->flight_number,
                'route' => $this->swapRequest->publishedTrip->flight->departure_airport . ' → ' . 
                          $this->swapRequest->publishedTrip->flight->arrival_airport,
            ],
        ];
    }
}
