<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
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
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $user->update(['status' => $request->status]);

        return redirect()->route('activation')->with('success', 'User status updated.');
    }
}
