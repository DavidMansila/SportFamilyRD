<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $table = 'training_requests';

    protected $fillable = [
        'user_id',
        'trainer_id',
        'sport_level',
        'description',
        'status',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
