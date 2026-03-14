<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishedTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_id',
        'user_trip_id',
        'status',
        'notes',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
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
}
