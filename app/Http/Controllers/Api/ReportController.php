<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReportUserRequest;
use App\Models\Report;
use App\Models\User;
use App\Services\MobileNotificationService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private MobileNotificationService $mobileNotificationService)
    {
    }

    public function reportUser(ReportUserRequest $request)
    {
        // Check if user has already reported this user
        $existingReport = Report::where('reporter_id', $request->user()->id)
            ->where('reported_user_id', $request->reported_user_id)
            ->whereIn('status', ['pending', 'reviewed'])
            ->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => __('messages.already_reported'),
            ], 400);
        }

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reported_user_id' => $request->reported_user_id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        $moderators = User::role(['admin', 'super-admin', 'support_moderator'])->get();
        $this->mobileNotificationService->createForUsers(
            $moderators,
            'New Report Submitted',
            'A new report requires moderation review.',
            'report',
            'report_alert_sound.mp3',
            ['report_id' => (string) $report->id]
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.user_reported'),
            'data' => $report,
        ], 201);
    }

    public function myReports(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $reports = Report::where('reporter_id', $request->user()->id)
            ->with('reportedUser')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($report) {
                return [
                    'id' => $report->id,
                    'reported_user' => [
                        'id' => $report->reportedUser->id,
                        'name' => $report->reportedUser->full_name,
                        'employee_id' => $report->reportedUser->employee_id,
                    ],
                    'reason' => $report->reason,
                    'details' => $report->details,
                    'status' => $report->status,
                    'resolution' => $report->resolution,
                    'created_at' => $report->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $reports->items(),
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
            ],
        ]);
    }
}
