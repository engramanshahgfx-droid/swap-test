<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReportUserRequest;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
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

        return response()->json([
            'success' => true,
            'message' => __('messages.user_reported'),
            'data' => $report,
        ], 201);
    }

    public function myReports(Request $request)
    {
        $reports = Report::where('reporter_id', $request->user()->id)
            ->with('reportedUser')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {
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
            'data' => $reports,
        ]);
    }
}
