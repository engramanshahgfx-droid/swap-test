<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountExpiryNotice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $stage)
    {
    }

    public function build(): self
    {
        $subject = match ($this->stage) {
            '30_days' => 'Your account expires in 30 days',
            '15_days' => 'Your account expires in 15 days',
            '7_days' => 'Your account expires in 7 days',
            '3_days' => 'URGENT: Your account expires in 3 days',
            '1_day' => 'LAST DAY: Your account expires tomorrow',
            default => 'Your account activation status has changed',
        };

        return $this->subject($subject)
            ->view('emails.account-expiry-notice')
            ->with([
                'user' => $this->user,
                'stage' => $this->stage,
                'activationEndDate' => $this->user->activation_end_date,
            ]);
    }
}
