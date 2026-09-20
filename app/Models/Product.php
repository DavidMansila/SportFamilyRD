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

        static::deleted(function (self $producto) {
            CacheDeContenido::olvidar(CacheDeContenido::PRODUCTOS);

            // Las lineas de carrito que apuntaban a esto se van con el.
            //
            // cart_items es polimorfica (item_type + item_id), asi que no puede
            // tener una clave foranea de verdad y Postgres no borra nada en
            // cascada. Las lineas se quedaban apuntando a un id inexistente:
            // getCart() las devolvia con 'item' => null, el carrito las pintaba
            // como "Item no disponible" con precio 0, y seguian contando en el
            // numero de articulos del cabecero. Para siempre, porque nada las
            // limpiaba salvo que la persona las borrase a mano.
            \App\Models\CartItem::where('item_type', 'product')
                ->where('item_id', $producto->id)
                ->delete();
        });
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