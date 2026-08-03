<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoster extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'line_number',
        'position',
        'file_name',
        'file_path',
        'total_duties',
        'total_flight_hours',
        'total_credit_hours',
        'raw_text',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_duties' => 'integer',
        'year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trips()
    {
        return $this->hasMany(UserTrip::class, 'user_roster_id');
    }
}
