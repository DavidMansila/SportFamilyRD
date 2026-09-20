<?php

namespace App\Models;

use App\Support\CacheDeContenido;

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

    /**
     * Limpia el cache de los endpoints publicos cuando cambian los usuarios.
     *
     * Va aqui y no en el controlador a proposito: asi se entera cualquier
     * escritura, venga del panel de admin, de un seeder, del scraper o de
     * tinker. Si estuviera en los controladores, bastaria con anadir una
     * ruta nueva y olvidarse para que la pagina mostrara datos viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn() => CacheDeContenido::olvidar(CacheDeContenido::USUARIOS));
        static::deleted(fn() => CacheDeContenido::olvidar(CacheDeContenido::USUARIOS));
    }

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
    

    /**
     * Conversaciones en las que esta cuenta participa COMO ENTRENADOR.
     *
     * Va a traves de la tabla 'trainer' a proposito. Antes era un
     * hasMany(Chat::class, 'trainer_id'), que compara chats.trainer_id -la
     * clave de la tabla 'trainer'- contra users.id: dos espacios de
     * identificadores distintos. No solo devolvia los chats equivocados, es que
     * podia devolver la conversacion de OTRA persona, la del entrenador cuyo id
     * de ficha coincidiera por casualidad con el id de usuario de esta cuenta.
     *
     * Hoy no la llama nadie, y por eso el fallo nunca se noto; el problema es
     * que el nombre invita a usarla.
     */
    public function chatsAsTrainer()
    {
        return $this->hasManyThrough(
            Chat::class,
            Trainer::class,
            'user_id',    // trainer.user_id -> users.id
            'trainer_id', // chats.trainer_id -> trainer.id
            'id',
            'id'
        );
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
