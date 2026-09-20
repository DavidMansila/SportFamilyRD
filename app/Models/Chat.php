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
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('trainer_id', $userId);
        });
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->noLeidos()
            ->where('sender_id', '!=', \Illuminate\Support\Facades\Auth::user()->id)
            ->count();
    }

    public function getOtherUserAttribute()
    {
        if (Auth::check() && Auth::user()->id == $this->user_id) {
            return $this->trainer;
        }
        return $this->user;
    }
}
