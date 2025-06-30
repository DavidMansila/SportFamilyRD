<?php

namespace App\Models;

use App\Models\Trainer;
use App\Models\Achievement;
use App\Models\Specialty;
use App\Models\SavedNews;
use App\Notifications\VerifyEmail;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


// class User extends Authenticatable
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
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


    public function savedNews()
    {
        return $this->hasMany(SavedNews::class);
    }



    public function chatsAsUser()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }
    

    public function chatsAsTrainer()
    {
        return $this->hasMany(Chat::class, 'trainer_id');
    }


    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Override the default email verification notification to include user_id in the link
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
