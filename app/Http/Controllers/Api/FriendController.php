<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Get user's friend list with same_roster indicator.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $friendRecords = Friend::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('friend_id', $user->id);
        })->where('status', 'accepted')->get();

        $friendIds = $friendRecords->map(function ($record) use ($user) {
            return $record->user_id === $user->id ? $record->friend_id : $record->user_id;
        })->unique();

        $friends = User::whereIn('id', $friendIds)
            ->with(['airline', 'position'])
            ->get()
            ->map(function ($friend) use ($user) {
                return [
                    'id' => $friend->id,
                    'full_name' => $friend->full_name,
                    'name' => $friend->full_name,
                    'employee_id' => $friend->employee_id,
                    'airline' => $friend->airline?->name,
                    'position' => $friend->position?->name,
                    'same_roster' => $user->sharesRosterWith($friend),
                    'is_friend' => true,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $friends,
                'total' => $friends->count(),
            ],
        ]);
    }

    /**
     * Add friend by user ID (supports URL param or request body).
     */
    public function addFriend(Request $request, $userId = null)
    {
        $user = $request->user();
        $targetId = $userId
            ?: ($request->input('user_id')
            ?: ($request->input('userId')
            ?: ($request->input('friend_id')
            ?: ($request->input('friendId')
            ?: ($request->input('target_id')
            ?: ($request->input('targetId')
            ?: ($request->input('id')
            ?: $request->input('user'))))))));

        if (!$targetId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $targetUser = User::find($targetId);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->id === (int) $targetId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add yourself as a friend.',
            ], 422);
        }

        $existing = Friend::where(function ($query) use ($user, $targetId) {
            $query->where(function ($q) use ($user, $targetId) {
                $q->where('user_id', $user->id)->where('friend_id', $targetId);
            })->orWhere(function ($q) use ($user, $targetId) {
                $q->where('user_id', $targetId)->where('friend_id', $user->id);
            });
        })->first();

        if ($existing) {
            $existing->update(['status' => 'accepted']);
        } else {
            Friend::create([
                'user_id' => $user->id,
                'friend_id' => $targetId,
                'status' => 'accepted',
            ]);
        }

        $sameRoster = $user->sharesRosterWith($targetUser);

        return response()->json([
            'success' => true,
            'message' => 'Friend added successfully.',
            'data' => [
                'user_id' => $targetUser->id,
                'full_name' => $targetUser->full_name,
                'is_friend' => true,
                'same_roster' => $sameRoster,
            ],
        ]);
    }

    /**
     * Toggle friend status (add if not friend, remove if friend).
     */
    public function toggleFriend(Request $request, $userId = null)
    {
        $user = $request->user();
        $targetId = $userId
            ?: ($request->input('user_id')
            ?: ($request->input('userId')
            ?: ($request->input('friend_id')
            ?: ($request->input('friendId')
            ?: ($request->input('target_id')
            ?: ($request->input('targetId')
            ?: ($request->input('id')
            ?: $request->input('user'))))))));

        if (!$targetId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        $targetUser = User::find($targetId);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->id === (int) $targetId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot toggle friend status with yourself.',
            ], 422);
        }

        $existing = Friend::where(function ($query) use ($user, $targetId) {
            $query->where(function ($q) use ($user, $targetId) {
                $q->where('user_id', $user->id)->where('friend_id', $targetId);
            })->orWhere(function ($q) use ($user, $targetId) {
                $q->where('user_id', $targetId)->where('friend_id', $user->id);
            });
        })->first();

        if ($existing && $existing->status === 'accepted') {
            $existing->delete();
            $isFriend = false;
            $message = 'Friend removed successfully.';
        } else {
            if ($existing) {
                $existing->update(['status' => 'accepted']);
            } else {
                Friend::create([
                    'user_id' => $user->id,
                    'friend_id' => $targetId,
                    'status' => 'accepted',
                ]);
            }
            $isFriend = true;
            $message = 'Friend added successfully.';
        }

        $sameRoster = $user->sharesRosterWith($targetUser);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user_id' => $targetUser->id,
                'full_name' => $targetUser->full_name,
                'is_friend' => $isFriend,
                'same_roster' => $sameRoster,
            ],
        ]);
    }

    /**
     * Remove friend.
     */
    public function removeFriend(Request $request, $userId = null)
    {
        $user = $request->user();
        $targetId = $userId
            ?: ($request->input('user_id')
            ?: ($request->input('userId')
            ?: ($request->input('friend_id')
            ?: ($request->input('friendId')
            ?: ($request->input('target_id')
            ?: ($request->input('targetId')
            ?: ($request->input('id')
            ?: $request->input('user'))))))));

        if (!$targetId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required.',
            ], 422);
        }

        Friend::where(function ($query) use ($user, $targetId) {
            $query->where(function ($q) use ($user, $targetId) {
                $q->where('user_id', $user->id)->where('friend_id', $targetId);
            })->orWhere(function ($q) use ($user, $targetId) {
                $q->where('user_id', $targetId)->where('friend_id', $user->id);
            });
        })->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully.',
        ]);
    }
}
