<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['id','titulo', 'contenido', 'imagen',  'user_id', 'likes_quantity'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}


class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'texto', 'fecha'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}


class Reply extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'texto', 'fecha' ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
