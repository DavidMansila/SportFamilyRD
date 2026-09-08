<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\Reply;

class Comment extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los comentarios (afectan el ranking de posts populares).
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


    protected $withCount = ['likes'];

    protected $fillable = [
        'id',
        'post_id',
        'user_id',
        'texto',
        'created_at',
        'updated_at',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
