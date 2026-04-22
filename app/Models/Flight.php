<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_airport_id',
        'arrival_airport_id',
        'departure_date',
        'arrival_date',
        'departure_time',
        'arrival_time',
        'airline_id',
        'plane_type_id',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function planeType()
    {
        return $this->belongsTo(PlaneType::class);
    }

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    public function assignedCrew()
    {
        return $this->hasMany(UserTrip::class);
    }

    public function publishedTrips()
    {
        return $this->hasMany(PublishedTrip::class);
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        return $hours . 'h ' . $minutes . 'm';
    }
}
