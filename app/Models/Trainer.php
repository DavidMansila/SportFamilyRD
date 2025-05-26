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
        'achievements',
        'schedule',
        'cost',
    ];
}
