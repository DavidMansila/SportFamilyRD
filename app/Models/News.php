<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los noticias.
     *
     * Va aqui y no en el controlador a proposito: asi se entera cualquier
     * escritura, venga del panel de admin, de un seeder, del scraper o de
     * tinker. Si estuviera en los controladores, bastaria con anadir una
     * ruta nueva y olvidarse para que la pagina mostrara datos viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn() => CacheDeContenido::olvidar(CacheDeContenido::NOTICIAS));
        static::deleted(fn() => CacheDeContenido::olvidar(CacheDeContenido::NOTICIAS));
    }

    use SoftDeletes;

    protected $table = 'NewsScrapping';

    protected $fillable = [
        'title',
        'description',
        'author',
        'source',
        'url',
        'image',
        'category',
        'published_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function savedByUsers()
    {
        return $this->hasMany(SavedNews::class, 'news_id', 'id');
    }
}
