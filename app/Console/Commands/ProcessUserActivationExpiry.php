<?php

namespace App\Console\Commands;

use App\Mail\AccountExpiryNotice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessUserActivationExpiry extends Command
{
    protected $signature = 'users:process-activation-expiry';

    protected $description = 'Process user activation expiry, grace period, and status transitions';

    public function handle(): int
    {
        $users = User::query()
            ->where('status', '!=', 'blocked')
            ->where('status', '!=', 'suspended')
            ->whereNotNull('activation_end_date')
            ->get();

        foreach ($users as $user) {
            $user->processActivationExpiry();

            if ($user->status === 'active' && $user->activation_end_date) {
                $daysRemaining = now()->diffInDays($user->activation_end_date, false);
                $stage = match (true) {
                    $daysRemaining <= 1 => '1_day',
                    $daysRemaining <= 3 => '3_days',
                    $daysRemaining <= 7 => '7_days',
                    $daysRemaining <= 15 => '15_days',
                    $daysRemaining <= 30 => '30_days',
                    default => null,
                };

                if ($stage && $user->last_expiry_notification_stage !== $stage) {
                    Mail::to($user->email)->send(new AccountExpiryNotice($user, $stage));
                    $user->last_expiry_notification_stage = $stage;
                    $user->last_expiry_notification_at = now();
                    $user->save();
                }
            }
        }

        $this->info('Processed ' . $users->count() . ' users for activation expiry.');

        return self::SUCCESS;
    }
}
