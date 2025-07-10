<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'title',
        'description',
        'achievement_date',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
