<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationSettingsRequest;
use App\Http\Requests\Api\UpdatePrivacySettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserSettingsController extends Controller
{
    public function updateNotificationSettings(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $data = [
            'swap_requests' => $request->input('swap_requests', true),
            'messages' => $request->input('messages', true),
            'system_updates' => $request->input('system_updates', true),
            'updated_at' => now(),
        ];

        DB::table('notification_settings')->updateOrInsert([
            'user_id' => $user->id,
        ], $data + ['user_id' => $user->id, 'created_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Notification settings updated.']);
    }

    public function updatePrivacySettings(UpdatePrivacySettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $update = [];
        if ($request->exists('hide_employee_id')) {
            $update['hide_employee_id'] = $request->input('hide_employee_id') ? true : false;
        }
        if ($request->exists('show_online_status')) {
            $update['show_online_status'] = $request->input('show_online_status') ? true : false;
        }
        if ($request->exists('allow_messages_from')) {
            $update['allow_messages_from'] = $request->input('allow_messages_from');
        }

        if (!empty($update)) {
            DB::table('users')->where('id', $user->id)->update($update + ['updated_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Privacy settings updated.']);
    }
}
