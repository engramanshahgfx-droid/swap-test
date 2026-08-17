<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
        'activation_start_date',
        'activation_end_date',
        'activation_type',
        'is_permanent',
        'grace_period_days',
        'grace_period_end_date',
        'last_expiry_notification_at',
        'last_expiry_notification_stage',
        'activation_notes',
        'otp_code',
        'otp_expires_at',
        'phone_verified_at',
        'firebase_uid',
        'device_token',
        'hide_employee_id',
        'show_online_status',
        'allow_messages_from',
        'willing_fly_days',
        'four_digit_code',
        'face_id_enabled',
        'biometric_public_key',
        'device_id',
        'biometric_login_enabled',
        'biometric_setup_at',
        'biometric_login_attempts',
        'biometric_locked_until',
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
        'activation_start_date' => 'date',
        'activation_end_date' => 'date',
        'grace_period_end_date' => 'date',
        'last_expiry_notification_at' => 'datetime',
        'hide_employee_id' => 'boolean',
        'show_online_status' => 'boolean',
        'is_permanent' => 'boolean',
        'willing_fly_days' => 'array',
        'biometric_login_enabled' => 'boolean',
        'face_id_enabled' => 'boolean',
        'biometric_setup_at' => 'datetime',
        'biometric_locked_until' => 'datetime',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function planeType()
    {
        return $this->belongsTo(PlaneType::class);
    }

    public function planeTypes()
    {
        return $this->belongsToMany(PlaneType::class, 'plane_type_user');
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

    public function tripPreferences()
    {
        return $this->hasOne(\App\Models\TripPreference::class);
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

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function isFriendWith($targetUserOrId): bool
    {
        $targetId = $targetUserOrId instanceof User ? $targetUserOrId->id : (int) $targetUserOrId;

        if ($this->id === $targetId) {
            return false;
        }

        return \App\Models\Friend::where(function ($query) use ($targetId) {
            $query->where(function ($q) use ($targetId) {
                $q->where('user_id', $this->id)->where('friend_id', $targetId);
            })->orWhere(function ($q) use ($targetId) {
                $q->where('user_id', $targetId)->where('friend_id', $this->id);
            });
        })->where('status', 'accepted')->exists();
    }

    public function sharesRosterWith($targetUserOrId): bool
    {
        $targetId = $targetUserOrId instanceof User ? $targetUserOrId->id : (int) $targetUserOrId;

        if ($this->id === $targetId) {
            return true;
        }

        // Check if both users share the same airline
        $targetUser = $targetUserOrId instanceof User ? $targetUserOrId : User::find($targetId);
        if (!$targetUser || ($this->airline_id && $targetUser->airline_id && $this->airline_id !== $targetUser->airline_id)) {
            return false;
        }

        // Check if both users uploaded rosters for the same month/year
        $myRosters = \App\Models\UserRoster::where('user_id', $this->id)->select('month', 'year')->get();
        if ($myRosters->isEmpty()) {
            return false;
        }

        foreach ($myRosters as $myRoster) {
            $matchingRoster = \App\Models\UserRoster::where('user_id', $targetId)
                ->where('month', $myRoster->month)
                ->where('year', $myRoster->year)
                ->exists();

            if ($matchingRoster) {
                return true;
            }
        }

        return false;
    }

    public function generateOtp()
    {
        $this->otp_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();

        return $this->otp_code;
    }

    public function verifyOtp($code)
    {
        if (empty($this->otp_code) || empty($this->otp_expires_at)) {
            return false;
        }

        $storedOtp = str_pad((string) $this->otp_code, 6, '0', STR_PAD_LEFT);
        $providedOtp = str_pad((string) $code, 6, '0', STR_PAD_LEFT);

        return hash_equals($storedOtp, $providedOtp) && $this->otp_expires_at->isFuture();
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isActivationExpired(): bool
    {
        if ($this->is_permanent) {
            return false;
        }

        if (empty($this->activation_end_date)) {
            return false;
        }

        return Carbon::parse($this->activation_end_date)->isPast();
    }

    public function isInGracePeriod(): bool
    {
        if ($this->is_permanent || empty($this->grace_period_end_date)) {
            return false;
        }

        return Carbon::parse($this->grace_period_end_date)->isFuture() || Carbon::parse($this->grace_period_end_date)->isToday();
    }

    public function activateForDuration(?int $months = null, ?int $years = null, ?string $customEndDate = null, bool $permanent = false, ?int $gracePeriodDays = 7): void
    {
        $this->status = 'active';
        $this->activation_type = $permanent ? 'permanent' : 'temporary';
        $this->is_permanent = $permanent;
        $this->activation_start_date = now()->toDateString();
        $this->grace_period_days = $gracePeriodDays ?? 7;
        $this->grace_period_end_date = null;
        $this->last_expiry_notification_at = null;
        $this->last_expiry_notification_stage = null;
        $this->activation_notes = null;

        if ($permanent) {
            $this->activation_end_date = null;
            $this->save();
            return;
        }

        if (!empty($customEndDate)) {
            $this->activation_end_date = Carbon::parse($customEndDate)->toDateString();
        } else {
            $durationMonths = max(0, (int) $months);
            $durationYears = max(0, (int) $years);
            $this->activation_end_date = now()->addMonths($durationMonths)->addYears($durationYears)->toDateString();
        }

        $this->save();
    }

    public function processActivationExpiry(): void
    {
        if ($this->is_permanent || empty($this->activation_end_date)) {
            return;
        }

        $today = now()->toDateString();
        $expiryDate = Carbon::parse($this->activation_end_date)->toDateString();

        if ($this->status === 'active' && $expiryDate < $today) {
            $this->status = 'expired';
            $this->grace_period_end_date = Carbon::parse($this->activation_end_date)->addDays($this->grace_period_days ?? 7)->toDateString();
            $this->save();
            return;
        }

        if ($this->status === 'expired' && !empty($this->grace_period_end_date) && Carbon::parse($this->grace_period_end_date)->toDateString() < $today) {
            $this->status = 'inactive';
            $this->save();
        }
    }

    public function getActivationStatusLabelAttribute(): string
    {
        if ($this->is_permanent) {
            return 'permanent';
        }

        if ($this->status === 'expired' || $this->isActivationExpired()) {
            return 'expired';
        }

        if ($this->status === 'active' && !empty($this->activation_end_date)) {
            $days = now()->diffInDays(Carbon::parse($this->activation_end_date), false);
            if ($days <= 7 && $days >= 0) {
                return 'expiring_soon';
            }
        }

        return $this->status;
    }

    public function getActivationDurationLabelAttribute(): string
    {
        if ($this->is_permanent) {
            return 'Permanent / unlimited activation';
        }

        if (empty($this->activation_start_date) || empty($this->activation_end_date)) {
            return 'No expiry set';
        }

        $startDate = Carbon::parse($this->activation_start_date);
        $endDate = Carbon::parse($this->activation_end_date);

        if ($endDate->lt($startDate)) {
            return 'Custom expiry date';
        }

        $years = $startDate->diffInYears($endDate);
        $months = $startDate->copy()->addYears($years)->diffInMonths($endDate);

        if ($years > 0 && $months > 0) {
            return $years . ' year' . ($years === 1 ? '' : 's') . ' and ' . $months . ' month' . ($months === 1 ? '' : 's');
        }

        if ($years > 0) {
            return $years . ' year' . ($years === 1 ? '' : 's');
        }

        if ($months > 0) {
            return $months . ' month' . ($months === 1 ? '' : 's');
        }

        return 'Less than a month';
    }
}
