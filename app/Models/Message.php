<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    protected $fillable = ['chat_id', 'sender_type','sender_id', 'message', 'read'];

    protected $casts = ['read' => 'boolean'];

    /**
     * Literal booleano para SQL, sin pasar por los parametros enlazados.
     *
     * Laravel convierte todo booleano de PHP a entero al preparar los bindings
     * (Connection::prepareBindings) y lo manda como PDO::PARAM_INT. En MySQL y
     * en SQLite da igual, porque ahi 0 y 1 SON los booleanos; en Postgres, en
     * cambio, 'read' es una columna boolean de verdad y la consulta revienta:
     *
     *   where "read" = 0   -> operator does not exist: boolean = integer
     *   set "read" = 1     -> column "read" is of type boolean...
     *
     * Por eso el chat funcionaba en local (SQLite/MySQL) y en produccion
     * (Supabase/Postgres) devolvia un 500 que tumbaba la bandeja entera: sin
     * ningun chat en la tabla nadie lo noto, porque la consulta del contador de
     * no leidos solo se ejecuta si hay al menos una conversacion.
     *
     * Una expresion cruda no se enlaza: el literal va tal cual y el nombre de
     * la columna lo sigue entrecomillando el grammar de cada motor.
     */
    public static function booleano(bool $valor): \Illuminate\Database\Query\Expression
    {
        return DB::raw($valor ? 'true' : 'false');
    }

    /**
     * Mensajes sin leer.
     */
    public function scopeNoLeidos($query)
    {
        return $query->where('read', '=', self::booleano(false));
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
