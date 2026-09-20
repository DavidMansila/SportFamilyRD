<?php

namespace App\Models;

use App\Support\CacheDeContenido;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
    // Sin 'id': dejar la clave primaria en $fillable permite fijarla desde
    // fuera en cuanto alguien escriba create($request->all()). Hoy todas las
    // llamadas pasan arrays explicitos, asi que quitarlo no cambia nada.
    protected $fillable = ['titulo', 'contenido', 'user_id', 'likes_quantity', 'imagen', 'video', 'categoria'];

    /**
     * Guarda la imagen de una entidad y devuelve su nombre, o null si no se
     * pudo guardar.
     *
     * EL ORDEN IMPORTA. Antes se borraba el contenido anterior del directorio y
     * DESPUES se subía el nuevo fichero, sin mirar el resultado. El disco
     * 'public' esta configurado con 'throw' => false y 'report' => false
     * (config/filesystems.php), asi que una subida fallida -credenciales
     * caducadas, cuota agotada, corte de red contra Supabase Storage- no lanza
     * excepcion: devuelve false en silencio. Con el orden antiguo eso dejaba a
     * la persona sin la foto vieja (ya borrada) y sin la nueva, y ademas el
     * llamador guardaba en la base el nombre del fichero inexistente, porque
     * hashName() lo devuelve igual.
     *
     * Ahora se sube primero, se comprueba, y solo con el fichero nuevo ya
     * escrito se limpian los anteriores. Si la subida falla no se toca nada y
     * se devuelve null, para que quien llama no escriba un nombre que no apunta
     * a ningun sitio.
     */
    public static function addImages($image, $id, $model): ?string
    {
        $path = "/$model/$id";

        if ($image->store($path, 'public') === false) {
            Log::error('No se pudo guardar la imagen en el disco publico', [
                'directorio' => $path,
                'disco' => config('filesystems.disks.public.driver'),
            ]);

            return null;
        }

        $imageName = $image->hashName();

        // Limpieza de las versiones anteriores, saltandose la recien subida.
        foreach (Storage::disk('public')->files($path) as $file) {
            if (basename($file) !== $imageName) {
                Storage::disk('public')->delete($file);
            }
        }

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
