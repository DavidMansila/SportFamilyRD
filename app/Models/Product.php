<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los productos.
     *
     * Va aqui y no en el controlador a proposito: asi se entera cualquier
     * escritura, venga del panel de admin, de un seeder, del scraper o de
     * tinker. Si estuviera en los controladores, bastaria con anadir una
     * ruta nueva y olvidarse para que la pagina mostrara datos viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn() => CacheDeContenido::olvidar(CacheDeContenido::PRODUCTOS));
        static::deleted(fn() => CacheDeContenido::olvidar(CacheDeContenido::PRODUCTOS));
    }

    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'stock',
        'price',
        'category',
        'image',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];
}