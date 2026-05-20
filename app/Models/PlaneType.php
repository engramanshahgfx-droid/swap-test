<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaneType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'airline_id', 'capacity', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'plane_type_user');
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
