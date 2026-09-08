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
        static::deleted(fn() => CacheDeContenido::olvidar(CacheDeContenido::CALENDARIO));
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
