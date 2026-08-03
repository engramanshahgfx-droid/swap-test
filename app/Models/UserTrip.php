<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTrip extends Model
{
    use HasFactory;

    protected $table = 'user_trips';

    protected $fillable = [
        'user_id',
        'flight_id',
        'user_roster_id',
        'pairing_number',
        'duty_type',
        'report_time',
        'release_time',
        'layover_location',
        'layover_duration',
        'legs',
        'status',
        'role',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
        'legs' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function roster()
    {
        return $this->belongsTo(UserRoster::class, 'user_roster_id');
    }

    public function publishedTrips()
    {
        return $this->hasMany(PublishedTrip::class, 'user_trip_id');
    }
}
