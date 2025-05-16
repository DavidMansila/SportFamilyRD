<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    //
    protected $fillable = ['comment_id', 'texto', 'user_id', 'created_at', 'updated_at'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
    
}
