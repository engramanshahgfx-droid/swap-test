<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected static $adminRoles = [
        'super-admin',
        'crew_manager',
        'hr_manager',
        'operations_manager',
        'support_moderator',
        'data_analyst',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Cache the result for this request to prevent multiple lookups
        if (isset($this->attributes['_can_access_panel_cached'])) {
            return $this->attributes['_can_access_panel_cached'];
        }

        // Eagerly load roles with a timeout protection
        $rolesLoaded = false;
        try {
            $userRoles = $this->roles()->limit(10)->pluck('name')->toArray();
            $rolesLoaded = true;
        } catch (\Exception $e) {
            \Log::error('Error loading user roles: ' . $e->getMessage());
            // Default to false if roles can't be loaded
            $this->attributes['_can_access_panel_cached'] = false;
            return false;
        }

        $result = !empty(array_intersect($userRoles, static::$adminRoles));
        $this->attributes['_can_access_panel_cached'] = $result;
        
        return $result;
    }

    public function getFilamentName(): string
    {
        return $this->full_name ?? 'User';
    }

    public function getNameAttribute(): string
    {
        return $this->full_name ?? 'User';
    }

    protected $attributes = [
        'status' => 'inactive',
    ];

    protected $fillable = [
        'employee_id',
        'full_name',
        'phone',
        'email',
        'country_base',
        'airline_id',
        'plane_type_id',
        'position_id',
        'password',
        'status',
        'otp_code',
        'otp_expires_at',
        'phone_verified_at',
        'firebase_uid',
        'device_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function planeType()
    {
        return $this->belongsTo(PlaneType::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function trips()
    {
        return $this->hasMany(UserTrip::class);
    }

    public function userTrips()
    {
        return $this->trips();
    }

    public function publishedTrips()
    {
        return $this->hasMany(PublishedTrip::class);
    }

    public function swapRequestsAsRequester()
    {
        return $this->hasMany(SwapRequest::class, 'requester_id');
    }

    public function swapRequestsAsResponder()
    {
        return $this->hasMany(SwapRequest::class, 'responder_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportedBy()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function generateOtp()
    {
        $this->otp_code = 123456;
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();
        
        return $this->otp_code;
    }

    public function verifyOtp($code)
    {
        return (int)$this->otp_code === (int)$code && $this->otp_expires_at->isFuture();
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}
