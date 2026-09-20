<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Aceptar una solicitud de entrenamiento tiene que dejar el chat abierto entre
 * el ATLETA y el entrenador.
 *
 * El chat lo creaba el frontend con un POST /chats posterior, mandando el
 * user_id del atleta en el cuerpo. Ese user_id dejo de usarse al cerrar el IDOR
 * de ChatController::store, que pasa a tomar el usuario autenticado: como quien
 * pulsa "aceptar" es el entrenador, el chat quedaba con user_id = entrenador
 * (un chat consigo mismo) y al atleta no le aparecia la burbuja.
 */
class ChatDeSolicitudAceptadaTest extends TestCase
{
    use RefreshDatabase;

    private function entrenador(User $user): Trainer
    {
        return Trainer::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '809-000-0000',
            'city_country' => 'Santo Domingo, RD',
            'sport_category' => 'Baloncesto',
            'experience' => '5 años',
            'level_of_certification' => 'basica',
        ]);
    }

    public function test_aceptar_la_solicitud_abre_el_chat_a_nombre_del_atleta(): void
    {
        Mail::fake();

        $atleta = User::factory()->create();
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $trainer = $this->entrenador($entrenadorUser);

        $solicitud = Training::create([
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'sport_level' => 'Principiante',
            'description' => 'Quiero empezar a entrenar',
            'status' => 'pending',
        ]);

        // Acepta el entrenador, que es quien tiene permiso.
        $this->actingAs($entrenadorUser)
            ->putJson("/api/training/{$solicitud->id}", ['status' => 'accepted'])
            ->assertOk();

        // El chat es del atleta con el entrenador, no del entrenador consigo mismo.
        $this->assertDatabaseHas('chats', [
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'status' => 'accepted',
        ]);

        // Y le aparece al atleta, que es quien se quedaba sin verlo.
        $this->actingAs($atleta)
            ->getJson('/api/chats')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_aceptar_dos_veces_no_duplica_el_chat(): void
    {
        Mail::fake();

        $atleta = User::factory()->create();
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $trainer = $this->entrenador($entrenadorUser);

        $solicitud = Training::create([
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'sport_level' => 'Principiante',
            'description' => 'Quiero empezar a entrenar',
            'status' => 'pending',
        ]);

        foreach (range(1, 2) as $intento) {
            $this->actingAs($entrenadorUser)
                ->putJson("/api/training/{$solicitud->id}", ['status' => 'accepted'])
                ->assertOk();
        }

        $this->assertSame(1, Chat::where('user_id', $atleta->id)
            ->where('trainer_id', $trainer->id)
            ->count());
    }
}
