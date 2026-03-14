<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'details',
        'status',
        'resolution',
        'admin_notes',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function markAsReviewed($resolution = null)
    {
        $this->status = 'reviewed';
        $this->resolution = $resolution;
        $this->reviewed_at = now();
        $this->save();
    }

    public function markAsResolved($resolution = null)
    {
        $this->status = 'resolved';
        $this->resolution = $resolution;
        $this->reviewed_at = now();
        $this->save();
    }
}
