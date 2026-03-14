<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'country', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function planeTypes()
    {
        return $this->hasMany(PlaneType::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
