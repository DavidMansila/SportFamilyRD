<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedNews extends Model
{
    protected $table = 'saved_news';
    protected $fillable = ['user_id', 'news_id'];
    public $timestamps = false;

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
