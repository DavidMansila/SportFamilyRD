<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    //
    protected $fillable = ['comment_id', 'texto', 'fecha' ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
    
}
