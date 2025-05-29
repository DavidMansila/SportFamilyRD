<?php

namespace App\Models;
use App\Models\Trainer;
use App\Models\Achievement;
use App\Models\Specialty;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',	
        'image',
        'phone',
        'location',
        'birthdate',
        'bio',
        'social_links',
        'achivements',
        
    ];

    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }

    public function achievements()
    {
        return $this->hasManyThrough(
            Achievement::class,
            Trainer::class,
            'user_id',    // Foreign key en la tabla Trainer 
            'trainer_id', // Foreign key en la tabla Achievement 
            'id',         // Local key en tabla User 
            'id'          // Local key en tabla Trainer 
        );
    }

    public function specialties()
    {
        return $this->hasManyThrough(
            Specialty::class,
            Trainer::class,
            'user_id',    // Foreign key en la tabla Trainer
            'trainer_id', // Foreign key en la tabla  Specialty 
            'id',         //Local key en tabla User
            'id'          //Local key en tabla Trainer
        );
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

}