<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $table = 'trainer';

    protected $fillable = [
        'user_id',
        'status',
        'name',
        'email',
        'phone',
        'city_country',
        'sport_category',
        'experience',
        'level_of_certification',
        'certificates_linked',
        'description',
        'schedule',
        'cost',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class, 'trainer_id');
    }

    public function specialties()
    {
        return $this->hasMany(Specialty::class, 'trainer_id');
    }
}
