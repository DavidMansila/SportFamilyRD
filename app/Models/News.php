<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use SoftDeletes;

    protected $table = 'newsscrapping';

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
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function savedByUsers()
    {
        return $this->hasMany(SavedNews::class, 'news_id', 'id');
    }
}
