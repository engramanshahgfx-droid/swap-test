<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use App\Services\MobileNotificationService;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function index(Request $request)
    {
        $query = User::with(['airline', 'position']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $selectedUserId = $request->integer('user_id') ?: optional($users->first())->id;
        $selectedUser = null;
        $swapHistory = collect();
        $userReports = collect();
        $reportedAgainst = collect();
        $chatMessages = collect();

        if ($selectedUserId) {
            $selectedUser = User::with([
                'airline',
                'position',
                'planeType',
                'trips.flight',
                'swapRequestsAsRequester.publishedTrip.flight',
                'swapRequestsAsRequester.responder',
                'swapRequestsAsResponder.publishedTrip.flight',
                'swapRequestsAsResponder.requester',
                'reports.reportedUser',
                'reportedBy.reporter',
            ])->find($selectedUserId);

            if ($selectedUser) {
                $swapHistory = $selectedUser->swapRequestsAsRequester
                    ->concat($selectedUser->swapRequestsAsResponder)
                    ->sortByDesc('created_at')
                    ->values()
                    ->take(10);

                $userReports = $selectedUser->reports->sortByDesc('created_at')->values()->take(10);
                $reportedAgainst = $selectedUser->reportedBy->sortByDesc('created_at')->values()->take(10);

                $chatMessages = Message::with('sender')
                    ->whereHas('conversation', function ($conversationQuery) use ($selectedUserId) {
                        $conversationQuery->where('user_one_id', $selectedUserId)
                            ->orWhere('user_two_id', $selectedUserId);
                    })
                    ->latest()
                    ->take(20)
                    ->get()
                    ->sortBy('created_at')
                    ->values();
            }
        }

        return view('pages.activation', compact('users', 'selectedUser', 'swapHistory', 'userReports', 'reportedAgainst', 'chatMessages'));
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,blocked,expired,suspended',
            'months' => 'nullable|integer|min:0|max:60',
            'years' => 'nullable|integer|min:0|max:10',
            'custom_end_date' => 'nullable|date',
            'permanent' => 'nullable|boolean',
            'grace_period_days' => 'nullable|integer|min:1|max:30',
        ]);

        $permanent = (bool) $request->boolean('permanent');

        if ($permanent || $request->filled('months') || $request->filled('years') || $request->filled('custom_end_date')) {
            $user->activateForDuration(
                $request->input('months'),
                $request->input('years'),
                $request->input('custom_end_date'),
                $permanent,
                $request->input('grace_period_days')
            );

            $message = $permanent ? 'User activated permanently.' : 'User activation updated.';
        } else {
            $user->update(['status' => $request->status]);
            $message = 'User status updated.';
        }

        $this->mobileNotificationService->createForUser(
            $user,
            'Account Status Updated',
            'Your account status is now: ' . $user->status,
            'system',
            'system_notification_sound.mp3',
            ['status' => (string) $user->status]
        );

        return redirect()->route('activation')->with('success', $message);
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        return redirect()->route('activation', $request->only('status'))->with('success', 'User deleted.');
    }
}
