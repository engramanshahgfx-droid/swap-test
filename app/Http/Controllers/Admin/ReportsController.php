<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Report;
use App\Services\MobileNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function index(Request $request)
    {
        $query = Report::with(['reporter.airline', 'reporter.position', 'reportedUser.airline', 'reportedUser.position', 'reviewedBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($reportQuery) use ($search) {
                $reportQuery->whereHas('reporter', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('reportedUser', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reports = $query->latest()->paginate(15)->withQueryString();
        $selectedReportId = $request->integer('report_id') ?: optional($reports->first())->id;
        $selectedReport = null;
        $relatedConversation = null;
        $conversationMessages = collect();

        if ($selectedReportId) {
            $selectedReport = Report::with([
                'reporter.airline',
                'reporter.position',
                'reportedUser.airline',
                'reportedUser.position',
                'reviewedBy',
            ])->find($selectedReportId);

            if ($selectedReport && $selectedReport->reporter_id && $selectedReport->reported_user_id) {
                $relatedConversation = Conversation::with(['userOne', 'userTwo'])
                    ->where(function ($conversationQuery) use ($selectedReport) {
                        $conversationQuery
                            ->where('user_one_id', $selectedReport->reporter_id)
                            ->where('user_two_id', $selectedReport->reported_user_id);
                    })
                    ->orWhere(function ($conversationQuery) use ($selectedReport) {
                        $conversationQuery
                            ->where('user_one_id', $selectedReport->reported_user_id)
                            ->where('user_two_id', $selectedReport->reporter_id);
                    })
                    ->latest('last_message_at')
                    ->first();

                $conversationMessages = $relatedConversation
                    ? $relatedConversation->messages()->with('sender')->latest()->take(100)->get()->sortBy('created_at')->values()
                    : collect();
            }
        }

        return view('pages.reports', compact('reports', 'selectedReport', 'relatedConversation', 'conversationMessages'));
    }

    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status'     => 'required|in:pending,reviewed,resolved',
            'resolution' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $payload = [
            'status'     => $request->status,
            'resolution' => $request->resolution,
        ];

        if (Schema::hasColumn('reports', 'admin_notes')) {
            $payload['admin_notes'] = $request->admin_notes;
        }

        if (Schema::hasColumn('reports', 'reviewed_by_id')) {
            $payload['reviewed_by_id'] = $request->user()?->id;
        }

        if (Schema::hasColumn('reports', 'reviewed_at')) {
            $payload['reviewed_at'] = $request->status === 'pending' ? null : Carbon::now();
        }

        $report->update($payload);

        return redirect()->route('reports', ['report_id' => $report->id])->with('success', 'Report status updated.');
    }

    public function moderate(Request $request, Report $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:ban,deactivate,reject',
            'ban_duration' => 'nullable|in:2_days,1_week,1_month,permanent',
            'reporter_message' => 'nullable|string|max:2000',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $reportedUser = $report->reportedUser;
        $status = 'reviewed';
        $resolution = $report->resolution;

        if ($validated['action'] === 'ban' && $reportedUser) {
            $reportedUser->update(['status' => 'blocked']);
            $status = 'resolved';
            $durationLabel = str_replace('_', ' ', $validated['ban_duration'] ?? 'manual');
            $resolution = 'User banned (' . $durationLabel . ')';

            $this->mobileNotificationService->createForUser(
                $reportedUser,
                'Account Restricted',
                'Your account has been restricted by a moderator.',
                'system',
                'system_notification_sound.mp3',
                ['reason' => 'ban']
            );
        }

        if ($validated['action'] === 'deactivate' && $reportedUser) {
            $reportedUser->update(['status' => 'inactive']);
            $status = 'resolved';
            $resolution = 'User deactivated by moderator';

            $this->mobileNotificationService->createForUser(
                $reportedUser,
                'Account Deactivated',
                'Your account has been deactivated by a moderator.',
                'system',
                'system_notification_sound.mp3',
                ['reason' => 'deactivate']
            );
        }

        if ($validated['action'] === 'reject') {
            $status = 'reviewed';
            $resolution = 'Report rejected by moderator';
        }

        $payload = [
            'status' => $status,
            'resolution' => $resolution,
        ];

        if (Schema::hasColumn('reports', 'admin_notes')) {
            $existingNotes = trim((string) $report->admin_notes);
            $actionNote = '[' . now()->format('Y-m-d H:i') . '] Action: ' . $validated['action'];
            $extraNote = trim((string) ($validated['admin_notes'] ?? ''));
            $composed = collect([$existingNotes, $actionNote, $extraNote])->filter()->implode("\n");
            $payload['admin_notes'] = $composed;
        }

        if (Schema::hasColumn('reports', 'reviewed_by_id')) {
            $payload['reviewed_by_id'] = $request->user()?->id;
        }

        if (Schema::hasColumn('reports', 'reviewed_at')) {
            $payload['reviewed_at'] = Carbon::now();
        }

        $report->update($payload);

        $messageText = trim((string) ($validated['reporter_message'] ?? ''));
        if ($messageText !== '') {
            $moderatorId = $request->user()->id;
            $reporterId = $report->reporter_id;

            $conversation = Conversation::where(function ($query) use ($moderatorId, $reporterId) {
                $query->where('user_one_id', $moderatorId)->where('user_two_id', $reporterId);
            })->orWhere(function ($query) use ($moderatorId, $reporterId) {
                $query->where('user_one_id', $reporterId)->where('user_two_id', $moderatorId);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_one_id' => $moderatorId,
                    'user_two_id' => $reporterId,
                    'last_message_at' => now(),
                ]);
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $moderatorId,
                'body' => $messageText,
            ]);

            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('reports', ['report_id' => $report->id])->with('success', 'Moderation action completed.');
    }
}
