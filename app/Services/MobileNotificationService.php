<?php

namespace App\Services;

use App\Models\MobileNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class MobileNotificationService
{
    private const SUPPORTED_SOUNDS = [
        'chat_message_sound.mp3',
        'swap_request_sound.mp3',
        'report_alert_sound.mp3',
        'system_notification_sound.mp3',
    ];

    public function __construct(private FirebaseNotificationService $firebaseNotificationService)
    {
    }

    public function getSupportedSounds(): array
    {
        return self::SUPPORTED_SOUNDS;
    }

    public function createForUsers(iterable $users, string $title, string $message, string $type, ?string $sound = null, array $payload = []): Collection
    {
        $usersCollection = collect($users)
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();

        $resolvedSound = $this->resolveSound($type, $sound);

        return $usersCollection->map(function (User $user) use ($title, $message, $type, $resolvedSound, $payload) {
            $notification = MobileNotification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'sound' => $resolvedSound,
                'is_read' => false,
                'payload' => $payload,
            ]);

            if (!empty($user->device_token)) {
                $this->firebaseNotificationService->sendToDevice(
                    $user->device_token,
                    $title,
                    $message,
                    array_merge($payload, [
                        'type' => $type,
                        'sound' => $resolvedSound,
                        'notification_id' => (string) $notification->id,
                    ]),
                    $resolvedSound
                );
            }

            return $notification;
        });
    }

    public function createForUser(User $user, string $title, string $message, string $type, ?string $sound = null, array $payload = []): MobileNotification
    {
        return $this->createForUsers([$user], $title, $message, $type, $sound, $payload)->first();
    }

    public function resolveSound(string $type, ?string $sound): string
    {
        if ($sound && in_array($sound, self::SUPPORTED_SOUNDS, true)) {
            return $sound;
        }

        return match ($type) {
            'chat', 'new_message' => 'chat_message_sound.mp3',
            'swap', 'swap_request', 'swap_accepted', 'swap_canceled', 'swap_rejected', 'swap_completed' => 'swap_request_sound.mp3',
            'report', 'report_alert' => 'report_alert_sound.mp3',
            default => 'system_notification_sound.mp3',
        };
    }
}
