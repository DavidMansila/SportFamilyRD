<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $withCount = ['likes'];
    protected $fillable = ['id', 'titulo', 'contenido', 'user_id', 'likes_quantity',  'imagen', 'video', 'categoria'];

    public static function addImages($image, $id, $model)
    {
        // Get the path to store the images
        $path = "/$model/$id";
        $files = Storage::disk('public')->files($path);

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        $image->store($path, 'public');

        $imageName = $image->hashName();
        return $imageName;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
