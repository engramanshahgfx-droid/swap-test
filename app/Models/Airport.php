<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'iata_code', 'city', 'country', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getCodeAttribute()
    {
        return $this->attributes['iata_code'] ?? null;
    }

    public function departureFlights()
    {
        return $this->hasMany(Flight::class, 'departure_airport_id');
    }

    public function arrivalFlights()
    {
        return $this->hasMany(Flight::class, 'arrival_airport_id');
    }
}
