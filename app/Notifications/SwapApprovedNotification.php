<?php

namespace App\Notifications;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SwapApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $swapRequest;
    protected $approvedBy;

    public function __construct(SwapRequest $swapRequest, string $approvedBy)
    {
        $this->swapRequest = $swapRequest;
        $this->approvedBy = $approvedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;
        $approverText = $this->approvedBy === 'owner' ? 'the trip owner' : 'a manager';

        return (new MailMessage)
            ->subject('Swap Request Approved - CrewSwap')
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line('Your swap request has been approved by ' . $approverText . '!')
            ->line('Flight: ' . $flight->flight_number)
            ->line('Route: ' . $flight->departure_airport . ' → ' . $flight->arrival_airport)
            ->line('Date: ' . $flight->date->format('Y-m-d'))
            ->action('View Details', url('/swap-requests/' . $this->swapRequest->id))
            ->line('Thank you for using CrewSwap!');
    }

    public function toArray($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;
        
        return [
            'type' => 'swap_approved',
            'approved_by' => $this->approvedBy,
            'swap_request_id' => $this->swapRequest->id,
            'flight' => [
                'number' => $flight->flight_number,
                'route' => $flight->departure_airport . ' → ' . $flight->arrival_airport,
                'date' => $flight->date->format('Y-m-d'),
            ],
            'message' => 'Your swap request for flight ' . $flight->flight_number . ' has been approved!',
        ];
    }
}
