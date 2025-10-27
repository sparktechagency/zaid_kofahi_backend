<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
            if (Event::where('slug', $post->slug)->exists()) {
                $post->slug = $post->slug . '-' . uniqid();
            }
        });
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset($this->image)
            : 'https://ui-avatars.com/api/?background=random&name=' . urlencode($this->title);
    }

}
