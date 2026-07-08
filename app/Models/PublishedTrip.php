<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserTrip;

class PublishedTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_id',
        'user_trip_id',
        'status',
        'notes',
        'vacation_type',
        'metadata',
        'published_at',
        'expires_at',
        'flight_number',
        'legs',
        'fly_type',
        'report_time',
        'offer_lo',
        'ask_lo',
        'details',
        'image_path',
        'allowed_swap_window',
        'is_urgent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'allowed_swap_window' => 'string',
        'is_urgent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function swapRequests()
    {
        return $this->hasMany(SwapRequest::class);
    }

    public function userTrip()
    {
        return $this->belongsTo(UserTrip::class);
    }

    public function isAvailable()
    {
        return in_array($this->status, ['active', 'available'], true);
    }

    public function allowsSwapWith(UserTrip $requesterTrip): bool
    {
        $allowed = $this->allowed_swap_window ?? 'same_day';

        $ownerDate = $this->flight?->departure_date;
        $requesterDate = $requesterTrip->flight?->departure_date;

        if (!$ownerDate || !$requesterDate) {
            return false;
        }

        if ($allowed === 'any') {
            return true;
        }

        if ($allowed === 'same_day') {
            return $ownerDate->toDateString() === $requesterDate->toDateString();
        }

        if ($allowed === 'day_before') {
            return $ownerDate->toDateString() === $requesterDate->toDateString() || $ownerDate->copy()->subDay()->toDateString() === $requesterDate->toDateString();
        }

        return false;
    }
}
