<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los likes (afectan el ranking de posts populares).
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

    protected $fillable = ['user_id', 'likeable_id', 'likeable_type'];


    public function likeable()
    {
        return $this->morphTo();
    }
}
