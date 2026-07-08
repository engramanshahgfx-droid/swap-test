<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripPreference extends Model
{
    use HasFactory;

    protected $table = 'trip_preferences';

    protected $fillable = [
        'user_id',
        'prefer_destinations',
        'exclude_destinations',
        'prefer_flight_types',
    ];

    protected $casts = [
        'prefer_destinations' => 'array',
        'exclude_destinations' => 'array',
        'prefer_flight_types' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
