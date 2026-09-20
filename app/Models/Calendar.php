<?php
	namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los eventos del calendario.
     *
     * Va aqui y no en el controlador a proposito: asi se entera cualquier
     * escritura, venga del panel de admin, de un seeder, del scraper o de
     * tinker. Si estuviera en los controladores, bastaria con anadir una
     * ruta nueva y olvidarse para que la pagina mostrara datos viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn() => CacheDeContenido::olvidar(CacheDeContenido::CALENDARIO));

        static::deleted(function (self $evento) {
            CacheDeContenido::olvidar(CacheDeContenido::CALENDARIO);

            // Las lineas de carrito que apuntaban a esto se van con el.
            //
            // cart_items es polimorfica (item_type + item_id), asi que no puede
            // tener una clave foranea de verdad y Postgres no borra nada en
            // cascada. Las lineas se quedaban apuntando a un id inexistente:
            // getCart() las devolvia con 'item' => null, el carrito las pintaba
            // como "Item no disponible" con precio 0, y seguian contando en el
            // numero de articulos del cabecero. Para siempre, porque nada las
            // limpiaba salvo que la persona las borrase a mano.
            \App\Models\CartItem::where('item_type', 'event')
                ->where('item_id', $evento->id)
                ->delete();
        });
    }

    use HasFactory;

    protected $fillable = [
        'Title',
        'date',
        'time',
        'place',
        'Description',
        'price',
        'image', 
        'quantity', 
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'item_id')->where('item_type', 'event');
    }
}
