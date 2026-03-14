<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SwapRequestRequest;
use App\Models\PublishedTrip;
use App\Models\SwapRequest;
use App\Services\SwapService;
use Illuminate\Http\Request;

class SwapController extends Controller
{
    protected $swapService;

    public function __construct(SwapService $swapService)
    {
        $this->swapService = $swapService;
    }

    public function requestSwap(Request $request)
    {
        $request->validate([
            'published_trip_id' => 'required|exists:published_trips,id',
            'requester_trip_id' => 'nullable|exists:user_trips,id',
            'message' => 'nullable|string|max:500',
        ]);

        $publishedTrip = PublishedTrip::with('user')->findOrFail($request->published_trip_id);

        try {
            $swapRequest = $this->swapService->requestSwap(
                $request->user(),
                $publishedTrip,
                $request->requester_trip_id,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => __('swap.request_submitted'),
                'data' => $swapRequest->load(['requester', 'responder', 'publishedTrip.flight']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function confirmSwap(Request $request, SwapRequest $swapRequest)
    {
        $user = $request->user();

        try {
            // Check if user is trip owner (responder) approving
            if ($user->id === $swapRequest->responder_id && $swapRequest->isPending()) {
                $swapRequest = $this->swapService->approveSwap($swapRequest);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Swap approved and sent for manager review.',
                    'data' => $swapRequest->load(['requester', 'responder', 'publishedTrip.flight']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('swap.not_authorized'),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function rejectSwap(Request $request, SwapRequest $swapRequest)
    {
        $user = $request->user();
        $reason = $request->reason;

        try {
            // Check if user is trip owner (responder) rejecting
            if ($user->id === $swapRequest->responder_id && $swapRequest->isPending()) {
                $swapRequest = $this->swapService->rejectSwap($swapRequest, $reason);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Swap request rejected.',
                    'data' => $swapRequest->load(['requester', 'responder', 'publishedTrip.flight']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('swap.not_authorized'),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancelSwap(Request $request, SwapRequest $swapRequest)
    {
        $user = $request->user();

        try {
            $swapRequest = $this->swapService->cancelSwap($swapRequest, $user, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Swap request canceled.',
                'data' => $swapRequest->load(['requester', 'responder', 'publishedTrip.flight']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
