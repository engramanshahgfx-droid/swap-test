<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileNotification;
use App\Models\User;
use App\Services\MobileNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|max:100',
            'is_read' => 'nullable|boolean',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = MobileNotification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (array_key_exists('is_read', $validated)) {
            $query->where('is_read', $validated['is_read']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $perPage = $validated['per_page'] ?? 20;
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($notifications->items())->map(function (MobileNotification $notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'sound' => $notification->sound,
                        'created_at' => optional($notification->created_at)->toDateTimeString(),
                        'read' => (bool) $notification->is_read,
                        'payload' => $notification->payload ?? new \stdClass(),
                    ];
                })->values(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|string|max:100',
            'sound' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        if (!$request->user()->hasAnyRole(['admin', 'crew_manager', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send notifications.',
            ], 403);
        }

        $recipientIds = collect($validated['user_ids'] ?? []);
        if (!empty($validated['user_id'])) {
            $recipientIds->push((int) $validated['user_id']);
        }

        $recipientIds = $recipientIds->unique()->values();

        if ($recipientIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'At least one recipient is required.',
            ], 422);
        }

        $users = User::whereIn('id', $recipientIds)->get();
        $notifications = $this->mobileNotificationService->createForUsers(
            $users,
            $validated['title'],
            $validated['message'],
            $validated['type'],
            $validated['sound'] ?? null,
            $validated['payload'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent successfully',
            'data' => [
                'count' => $notifications->count(),
                'notification_ids' => $notifications->pluck('id')->values(),
            ],
        ], 201);
    }

    public function markAsRead(Request $request, int $id)
    {
        $notification = MobileNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'id' => $notification->id,
                'read' => true,
            ],
        ]);
    }
}
