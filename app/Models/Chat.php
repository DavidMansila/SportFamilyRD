<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['user_id', 'trainer_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhere('trainer_id', $userId);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()
            ->where('read', false)
            ->where('sender_id', '!=', \Illuminate\Support\Facades\Auth::user()->id)
            ->count();
    }
}
