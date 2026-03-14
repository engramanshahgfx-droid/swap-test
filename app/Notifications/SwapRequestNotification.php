<?php

namespace App\Notifications;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SwapRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $swapRequest;
    protected $type;

    public function __construct(SwapRequest $swapRequest, string $type)
    {
        $this->swapRequest = $swapRequest;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;
        $fromUser = $this->swapRequest->fromUser;

        return (new MailMessage)
            ->subject('New Swap Request - CrewSwap')
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line($fromUser->full_name . ' has requested to swap for flight ' . $flight->flight_number)
            ->line('Route: ' . $flight->departure_airport . ' → ' . $flight->arrival_airport)
            ->line('Date: ' . optional($flight->departure_date)->format('Y-m-d'))
            ->action('View Request', url('/swap-requests/' . $this->swapRequest->id))
            ->line('Please review this request.');
    }

    public function toArray($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;
        
        return [
            'type' => $this->type,
            'swap_request_id' => $this->swapRequest->id,
            'from_user' => [
                'id' => $this->swapRequest->fromUser->id,
                'name' => $this->swapRequest->fromUser->full_name,
            ],
            'flight' => [
                'number' => $flight->flight_number,
                'route' => $flight->departure_airport . ' → ' . $flight->arrival_airport,
                'date' => optional($flight->departure_date)->format('Y-m-d'),
            ],
            'message' => $this->swapRequest->fromUser->full_name . ' has requested a swap for flight ' . $flight->flight_number,
        ];
    }
}
