<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'heading',
        'description',
        'image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'featured_image',
        'excerpt',
        'user',
        'content',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getFeaturedImageAttribute()
    {
        if (! $this->image) {
            return null;
        }

        $path = Storage::url($this->image);

        return url($path);
    }

    public function getExcerptAttribute()
    {
        return $this->description ? Str::limit(strip_tags($this->description), 150) : null;
    }

    public function getContentAttribute()
    {
        return $this->description;
    }

    public function getUserAttribute()
    {
        return [
            'full_name' => 'Admin',
        ];
    }
}
