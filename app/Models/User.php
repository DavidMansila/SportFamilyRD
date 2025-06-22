<?php

namespace App\Models;

use App\Models\Trainer;
use App\Models\Achievement;
use App\Models\Specialty;
use App\Models\SavedNews;

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



    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id')
            ->orWhereHas('trainer', function ($query) {
                $query->where('user_id', $this->id);
            });
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

    public function trainerChats()
    {
        return $this->hasMany(Chat::class, 'trainer_id');
    }

    public function userChats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }



    /**
     * Generate a new API token for the user
     */
    public function generateAuthToken()
    {
        $this->api_token = Str::random(60);
        $this->save();
        return $this->api_token;
    }

    /**
     * Clear the user's API token
     */
    public function clearAuthToken()
    {
        $this->api_token = null;
        $this->save();
    }
}
