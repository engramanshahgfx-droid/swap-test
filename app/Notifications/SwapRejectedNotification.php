<?php

namespace App\Notifications;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SwapRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $swapRequest;
    protected $rejectedBy;
    protected $reason;

    public function __construct(SwapRequest $swapRequest, string $rejectedBy, ?string $reason = null)
    {
        $this->swapRequest = $swapRequest;
        $this->rejectedBy = $rejectedBy;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;
        $rejecterText = $this->rejectedBy === 'owner' ? 'the trip owner' : 'a manager';

        $mail = (new MailMessage)
            ->subject('Swap Request Rejected - flightSwap ')
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line('Unfortunately, your swap request has been rejected by ' . $rejecterText . '.')
            ->line('Flight: ' . $flight->flight_number)
            ->line('Route: ' . $flight->departure_airport . ' → ' . $flight->arrival_airport);

        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail
            ->action('Browse Other Trips', url('/browse-trips'))
            ->line('You can browse other available trips.');
    }

    public function toArray($notifiable)
    {
        $flight = $this->swapRequest->publishedTrip->flight;

        return [
            'type' => 'swap_rejected',
            'rejected_by' => $this->rejectedBy,
            'reason' => $this->reason,
            'swap_request_id' => $this->swapRequest->id,
            'flight' => [
                'number' => $flight->flight_number,
                'route' => $flight->departure_airport . ' → ' . $flight->arrival_airport,
            ],
            'message' => 'Your swap request for flight ' . $flight->flight_number . ' has been rejected.',
        ];
    }
}
