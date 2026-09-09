<?php

namespace App\Models;

use App\Support\CacheDeContenido;
use Illuminate\Support\Facades\Cache;

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
        // Ademas de las claves del listado, hay que soltar la de ESTA noticia
        // en concreto: el texto completo se cachea por separado, con su id
        // (ver GET /news/{id}). Sin esto, editar una noticia cambiaria el
        // listado pero el pop-out seguiria mostrando el texto viejo.
        $olvidar = function ($noticia) {
            CacheDeContenido::olvidar(CacheDeContenido::NOTICIAS);
            Cache::forget('news-item-' . $noticia->id);
        };

        static::saved($olvidar);
        static::deleted($olvidar);
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
