<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'channel_id',
        'title',
        'description',
        'video_path',
        'thumbnail_path',
        'views',
    ];

    // Un video pertenece a un canal
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    // Un video tiene muchos likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Un video tiene muchos comentarios
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
?>