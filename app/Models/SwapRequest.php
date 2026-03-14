<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'responder_id',
        'published_trip_id',
        'requester_trip_id',
        'status',
        'manager_approval_status',
        'manager_notes',
        'manager_approved_by_id',
        'manager_approved_at',
        'message',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'manager_approved_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function fromUser()
    {
        return $this->requester();
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function toUser()
    {
        return $this->responder();
    }

    public function publishedTrip()
    {
        return $this->belongsTo(PublishedTrip::class);
    }

    public function requesterTrip()
    {
        return $this->belongsTo(UserTrip::class, 'requester_trip_id');
    }

    public function managerApprovedBy()
    {
        return $this->belongsTo(User::class, 'manager_approved_by_id');
    }

    public function getFromUserIdAttribute()
    {
        return $this->requester_id;
    }

    public function getToUserIdAttribute()
    {
        return $this->responder_id;
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return in_array($this->status, ['approved', 'approved_by_owner', 'manager_approved', 'completed'], true);
    }

    public function isAwaitingManagerApproval()
    {
        return $this->status === 'approved_by_owner' && $this->manager_approval_status === 'pending';
    }
}
