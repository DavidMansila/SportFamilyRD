<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los posts del foro.
     *
     * Va aqui y no en el controlador a proposito: asi se entera cualquier
     * escritura, venga del panel de admin, de un seeder, del scraper o de
     * tinker. Si estuviera en los controladores, bastaria con anadir una
     * ruta nueva y olvidarse para que la pagina mostrara datos viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn() => CacheDeContenido::olvidar(CacheDeContenido::FORO));
        static::deleted(fn() => CacheDeContenido::olvidar(CacheDeContenido::FORO));
    }

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
