<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'type',
        'popularity',
        'image',
        'short_description',
        'description',
        'requirements',
        'places',
        'sort_order',
    ];

    protected $casts = [
        'requirements' => 'array',
        'places' => 'array',
    ];
}
