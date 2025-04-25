<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['id','titulo', 'contenido', 'user_id', 'likes_quantity',  'imagen', 'video','categoria'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
   
    public static function addImages($image, $id)
    {
        // Get the path to store the images
        $path = "/posts/$id";
        $files = Storage::disk('public')->files($path);
       
        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        $image->store($path, 'public');

        $imageName = $image->hashName();
        return $imageName;
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
