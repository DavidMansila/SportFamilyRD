<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'description',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
