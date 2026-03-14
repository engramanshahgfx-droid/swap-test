<?php

namespace App\Services;

use App\Models\SwapRequest;
use App\Models\PublishedTrip;
use App\Models\UserTrip;
use App\Models\Flight;
use App\Models\User;
use App\Events\SwapRequested;
use App\Events\SwapApproved;
use App\Events\SwapRejected;
use App\Events\SwapCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwapService
{
    public function requestSwap(User $requester, PublishedTrip $publishedTrip, $requesterTripId = null, $message = null)
    {
        return DB::transaction(function () use ($requester, $publishedTrip, $requesterTripId, $message) {
            // Check if the trip is available
            if (!$publishedTrip->isAvailable()) {
                throw new \Exception('This trip is not available for swap.');
            }

            // Check if the users are qualified for the flight
            if (!$this->validateUserForFlight($requester, $publishedTrip->flight)) {
                throw new \Exception('You are not qualified for this flight.');
            }

            // Get requester_trip_id if not provided - use first available trip
            if (!$requesterTripId) {
                $requesterTrip = $requester->userTrips()
                    ->where('status', 'assigned')
                    ->first();
                $requesterTripId = $requesterTrip ? $requesterTrip->id : null;
            }

            $requesterTrip = UserTrip::where('id', $requesterTripId)
                ->where('user_id', $requester->id)
                ->first();

            if (!$requesterTrip) {
                throw new \Exception('You must have an assigned trip to request a swap.');
            }

            if ($publishedTrip->flight_id === $requesterTrip->flight_id) {
                throw new \Exception('You cannot request a swap for the same trip.');
            }

            // Create swap request
            $swapRequest = SwapRequest::create([
                'requester_id' => $requester->id,
                'responder_id' => $publishedTrip->user_id,
                'published_trip_id' => $publishedTrip->id,
                'requester_trip_id' => $requesterTrip->id,
                'status' => 'pending',
                'manager_approval_status' => 'pending',
                'message' => $message,
            ]);

            // Dispatch event
            try {
                event(new SwapRequested($swapRequest));
            } catch (\Exception $e) {
                // Event may not exist, continue
            }

            return $swapRequest;
        });
    }

    public function approveSwap(SwapRequest $swapRequest)
    {
        return DB::transaction(function () use ($swapRequest) {
            if (!$swapRequest->isPending()) {
                throw new \Exception('This swap request cannot be approved.');
            }

            $swapRequest->update([
                'status' => 'approved_by_owner',
                'manager_approval_status' => 'pending',
                'responded_at' => now(),
            ]);

            try {
                event(new SwapApproved($swapRequest, 'owner'));
            } catch (\Exception $e) {
                // Event may not exist, continue
            }

            return $swapRequest;
        });
    }

    public function rejectSwap(SwapRequest $swapRequest, $reason = null)
    {
        return DB::transaction(function () use ($swapRequest, $reason) {
            $swapRequest->update([
                'status' => 'rejected_by_owner',
                'responded_at' => now(),
            ]);

            // Keep trip active for other requests
            // $swapRequest->publishedTrip is already 'active'

            try {
                event(new SwapRejected($swapRequest, 'owner', $reason));
            } catch (\Exception $e) {
                // Event may not exist, continue
            }

            return $swapRequest;
        });
    }

    public function approveByManager(SwapRequest $swapRequest, User $manager, $notes = null)
    {
        return DB::transaction(function () use ($swapRequest, $manager, $notes) {
            if (!$swapRequest->isAwaitingManagerApproval()) {
                throw new \Exception('This swap request is not awaiting manager approval.');
            }

            $this->executeSwap($swapRequest);

            $swapRequest->update([
                'status' => 'completed',
                'manager_approval_status' => 'approved',
                'manager_notes' => $notes,
                'manager_approved_by_id' => $manager->id,
                'manager_approved_at' => now(),
            ]);

            $swapRequest->publishedTrip->update(['status' => 'closed']);

            try {
                event(new SwapApproved($swapRequest, 'manager'));
                event(new SwapCompleted($swapRequest));
            } catch (\Exception $e) {
                // Event may not exist, continue
            }

            return $swapRequest;
        });
    }

    public function rejectByManager(SwapRequest $swapRequest, User $manager, $notes = null)
    {
        return DB::transaction(function () use ($swapRequest, $manager, $notes) {
            if (!in_array($swapRequest->status, ['pending', 'approved_by_owner'], true) || $swapRequest->manager_approval_status !== 'pending') {
                throw new \Exception('This swap request cannot be rejected by a manager.');
            }

            $swapRequest->update([
                'status' => 'manager_rejected',
                'manager_approval_status' => 'rejected',
                'manager_notes' => $notes,
                'manager_approved_by_id' => $manager->id,
                'manager_approved_at' => now(),
            ]);

            try {
                event(new SwapRejected($swapRequest, 'manager', $notes));
            } catch (\Exception $e) {
                // Event may not exist, continue
            }

            return $swapRequest;
        });
    }

    protected function executeSwap(SwapRequest $swapRequest)
    {
        $publishedTrip = $swapRequest->publishedTrip;
        $ownerTrip = $publishedTrip->user_trip_id
            ? UserTrip::find($publishedTrip->user_trip_id)
            : UserTrip::where('user_id', $publishedTrip->user_id)
                ->where('flight_id', $publishedTrip->flight_id)
                ->first();

        $requesterTrip = $swapRequest->requesterTrip;

        if (!$ownerTrip || !$requesterTrip) {
            throw new \Exception('Unable to complete swap because one of the trip assignments is missing.');
        }

        $originalUserId = $ownerTrip->user_id;
        $requestingUserId = $requesterTrip->user_id;
        $ownerRole = $ownerTrip->role;
        $requesterRole = $requesterTrip->role;

        $ownerTrip->update([
            'user_id' => $requestingUserId,
            'status' => 'assigned',
            'role' => $ownerRole,
        ]);

        $requesterTrip->update([
            'user_id' => $originalUserId,
            'status' => 'assigned',
            'role' => $requesterRole,
        ]);

        // Log the swap
        Log::info('Flight swap executed', [
            'published_trip_id' => $publishedTrip->id,
            'owner_trip_id' => $ownerTrip->id,
            'requester_trip_id' => $requesterTrip->id,
            'original_user' => $originalUserId,
            'new_user' => $requestingUserId,
            'swap_request_id' => $swapRequest->id,
        ]);
    }

    protected function validateUserForFlight(User $user, Flight $flight)
    {
        // Check if user has correct qualifications
        return $user->airline_id === $flight->airline_id &&
               $user->plane_type_id === $flight->plane_type_id &&
               $user->isActive();
    }

    public function getUserEligibleTrips(User $user)
    {
        return PublishedTrip::whereIn('status', ['available', 'active'])
            ->whereHas('flight', function ($query) use ($user) {
                $query->where('airline_id', $user->airline_id)
                      ->where('plane_type_id', $user->plane_type_id);
            })
            ->where('user_id', '!=', $user->id)
            ->with(['user', 'flight'])
            ->get();
    }
}
