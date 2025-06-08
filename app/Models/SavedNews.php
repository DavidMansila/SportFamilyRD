<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedNews extends Model
{
    protected $table = 'saved_news';
    protected $fillable = ['user_id', 'news_id'];

    // Especificar la tabla correcta para la relación
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id', 'id');
    }
}
