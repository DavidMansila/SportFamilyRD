<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{

    protected $table = 'newsscrapping';
    //  
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'author',
        'source',
        'url',
        'image',
        'category',
        'published_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
