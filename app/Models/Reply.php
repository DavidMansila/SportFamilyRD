<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $withCount = ['likes'];

    // Sin timestamps: los gestiona Eloquent (ver la nota en Comment).
    protected $fillable = ['comment_id', 'texto', 'user_id'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
