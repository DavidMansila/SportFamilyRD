<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Chat extends Model
{
    protected $fillable = ['user_id', 'trainer_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }



    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }




    /**
     * Chats en los que participa $userId.
     *
     * El where/orWhere va AGRUPADO en un closure a proposito. Sin agrupar, al
     * encadenar este scope con cualquier otra condicion SQL aplica AND antes
     * que OR y la consulta deja de significar lo que aparenta:
     *
     *   ->forUser(7)->accepted()
     *     -> WHERE user_id = 7 OR (trainer_id = 7 AND status = 'accepted')
     *
     * es decir, el filtro de estado solo se aplicaba a una de las dos ramas.
     * Es la forma tipica de bug de autorizacion que no se ve en revision.
     */
    /**
     * (continuacion) Y la segunda rama compara contra la tabla 'trainer', no
     * contra la columna trainer_id.
     *
     * chats.trainer_id guarda la clave de la ficha de entrenador, no el id de
     * usuario: `orWhere('trainer_id', $userId)` mezclaba los dos espacios de
     * identificadores. Para un entrenador no devolvia sus conversaciones, y
     * podia colar la de otra persona si los numeros coincidian. Es la misma
     * condicion que ya usa ChatController::index, que si estaba bien resuelta.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereHas('trainer', function ($t) use ($userId) {
                    $t->where('user_id', $userId);
                });
        });
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // ELIMINADO: getUnreadCountAttribute().
    //
    // Un accessor con el mismo nombre que un alias de withCount lo PISA: al
    // leer $chat->unread_count, Eloquent prefiere el accessor y vuelve a
    // consultar la base, una vez por cada conversacion, tirando por tierra el
    // conteo agregado que ya venia resuelto en la consulta principal. El N+1
    // que se acababa de quitar del controlador reaparecia por aqui, y
    // silenciosamente: el resultado era correcto, solo que carisimo.
    //
    // Ademas hacia Auth::user()->id sin comprobar que hubiera sesion, asi que
    // cualquier acceso al atributo sin usuario autenticado reventaba con un
    // "Attempt to read property on null".
    //
    // El contador lo calcula ChatController::index con withCount, que es el
    // unico sitio que lo necesita y el unico que sabe para QUIEN se cuenta.

    public function getOtherUserAttribute()
    {
        if (Auth::check() && Auth::user()->id == $this->user_id) {
            return $this->trainer;
        }
        return $this->user;
    }
}
