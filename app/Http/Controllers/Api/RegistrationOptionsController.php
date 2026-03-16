<?php

namespace App\Http\Controllers\Api;

use App\Models\Airline;
use App\Models\PlaneType;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationOptionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $airlineId = $request->integer('airline_id');

        $airlines = Airline::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $planeTypes = PlaneType::query()
            ->with('airline:id,name')
            ->where('is_active', true)
            ->whereHas('airline', fn ($query) => $query->where('is_active', true))
            ->when($airlineId, fn ($query) => $query->where('airline_id', $airlineId))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'airline_id']);

        $positions = Position::query()
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'level']);

        return response()->json([
            'success' => true,
            'message' => 'Registration options fetched successfully.',
            'data' => [
                'airlines' => $airlines,
                'plane_types' => $planeTypes->map(fn (PlaneType $planeType) => [
                    'id' => $planeType->id,
                    'name' => $planeType->name,
                    'code' => $planeType->code,
                    'airline_id' => $planeType->airline_id,
                    'airline_name' => $planeType->airline?->name,
                ])->values(),
                'positions' => $positions,
            ],
        ]);
    }
}