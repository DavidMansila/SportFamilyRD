<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigurationUser extends Model
{
    use HasFactory;

    protected $table = 'configuration_user';

    protected $fillable = [
        'user_id',
        'configuration_id',
        'status',
    ];
}
