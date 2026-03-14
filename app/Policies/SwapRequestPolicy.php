<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SwapRequest;

class SwapRequestPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            'super-admin',
            'crew_manager',
            'operations_manager',
            'support_moderator',
            'flight_attendant',
            'purser',
        ]);
    }

    public function view(User $user, SwapRequest $swapRequest)
    {
        return $user->id === $swapRequest->from_user_id ||
               $user->id === $swapRequest->to_user_id ||
               $user->hasRole('crew_manager');
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['flight_attendant', 'purser']);
    }

    public function approveByOwner(User $user, SwapRequest $swapRequest)
    {
        return $user->id === $swapRequest->to_user_id &&
               $swapRequest->status === 'pending';
    }

    public function approveByManager(User $user, SwapRequest $swapRequest)
    {
        return $user->hasAnyRole(['crew_manager', 'operations_manager']) &&
               $swapRequest->status === 'approved_by_owner' &&
               $swapRequest->manager_approval_status === 'pending';
    }

    public function rejectByOwner(User $user, SwapRequest $swapRequest)
    {
        return $user->id === $swapRequest->to_user_id &&
               in_array($swapRequest->status, ['pending', 'approved_by_owner']);
    }

    public function rejectByManager(User $user, SwapRequest $swapRequest)
    {
        return $user->hasAnyRole(['crew_manager', 'operations_manager']) &&
               $swapRequest->manager_approval_status === 'pending';
    }
}
