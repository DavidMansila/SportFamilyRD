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





