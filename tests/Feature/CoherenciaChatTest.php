<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Coherencia de roles y de estados en el chat (segunda ronda de auditoria).
 *
 * El hilo comun de estos fallos es el mismo: tratar el user_type GLOBAL de la
 * cuenta como si fuera su papel EN UNA CONVERSACION concreta. Son dos cosas
 * distintas -un entrenador es el atleta del chat en el que el entrena- y ademas
 * el user_type cambia en caliente cuando se aprueba una solicitud de entrenador.
 */
class CoherenciaChatTest extends TestCase
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
            'experience' => '5 anos',
            'level_of_certification' => 'basica',
        ]);
    }

    private function solicitud(User $atleta, Trainer $trainer, string $status = 'pending'): Training
    {
        return Training::create([
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'sport_level' => 'Principiante',
            'description' => 'Quiero entrenar',
            'status' => $status,
        ]);
    }

    // ---------------------------------------------------------------- CH-3

    public function test_no_se_puede_abrir_un_chat_sin_solicitud_aceptada(): void
    {
        $atleta = User::factory()->create();
        $trainer = $this->entrenador(User::factory()->create(['user_type' => 'entrenador']));

        // Sin solicitud ninguna: el trainer_id es publico, sale en el directorio.
        $this->actingAs($atleta)
            ->postJson('/api/chats', ['trainer_id' => $trainer->id])
            ->assertStatus(403);

        // Con la solicitud rechazada tampoco.
        $this->solicitud($atleta, $trainer, 'rejected');

        $this->actingAs($atleta)
            ->postJson('/api/chats', ['trainer_id' => $trainer->id])
            ->assertStatus(403);

        $this->assertDatabaseCount('chats', 0);
    }

    public function test_con_solicitud_aceptada_si_se_puede_abrir(): void
    {
        $atleta = User::factory()->create();
        $trainer = $this->entrenador(User::factory()->create(['user_type' => 'entrenador']));
        $this->solicitud($atleta, $trainer, 'accepted');

        $this->actingAs($atleta)
            ->postJson('/api/chats', ['trainer_id' => $trainer->id])
            ->assertStatus(201);
    }

    // ---------------------------------------------------------------- CH-9

    public function test_rechazar_una_solicitud_ya_aceptada_cierra_el_chat(): void
    {
        Mail::fake();

        $atleta = User::factory()->create();
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $trainer = $this->entrenador($entrenadorUser);
        $solicitud = $this->solicitud($atleta, $trainer);

        $this->actingAs($entrenadorUser)
            ->putJson("/api/training/{$solicitud->id}", ['status' => 'accepted'])
            ->assertOk();

        $chat = Chat::where('user_id', $atleta->id)->firstOrFail();

        // El entrenador se echa atras.
        $this->actingAs($entrenadorUser)
            ->putJson("/api/training/{$solicitud->id}", ['status' => 'rejected'])
            ->assertOk();

        $this->assertSame('rejected', $chat->fresh()->status);

        // Ya no aparece en la bandeja...
        $this->actingAs($atleta)->getJson('/api/chats')->assertOk()->assertJsonCount(0);

        // ...y tampoco se puede seguir escribiendo en el por la puerta de atras.
        $this->actingAs($atleta)
            ->postJson("/api/chats/{$chat->id}/messages", ['message' => 'Hola?'])
            ->assertStatus(403);

        // Si vuelve a aceptar, la conversacion se reabre con su historial.
        $this->actingAs($entrenadorUser)
            ->putJson("/api/training/{$solicitud->id}", ['status' => 'accepted'])
            ->assertOk();

        $this->assertSame('accepted', $chat->fresh()->status);
        $this->assertSame($chat->id, Chat::where('user_id', $atleta->id)->firstOrFail()->id);
    }

    // ---------------------------------------------------------------- CH-4 / CH-5

    public function test_el_papel_del_remitente_sale_del_chat_no_del_rol_global(): void
    {
        Mail::fake();

        $atleta = User::factory()->create();
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $trainer = $this->entrenador($entrenadorUser);
        $solicitud = $this->solicitud($atleta, $trainer);

        $this->actingAs($entrenadorUser)
            ->putJson("/api/training/{$solicitud->id}", ['status' => 'accepted'])
            ->assertOk();

        $chat = Chat::where('user_id', $atleta->id)->firstOrFail();

        $this->actingAs($atleta)
            ->postJson("/api/chats/{$chat->id}/messages", ['message' => 'Hola entrenador'])
            ->assertOk();

        // Al atleta le aprueban su propia solicitud de entrenador: su user_type
        // global pasa a 'entrenador', pero en ESTE chat sigue siendo el atleta.
        $atleta->update(['user_type' => 'entrenador']);
        $this->entrenador($atleta->fresh());

        $this->actingAs($atleta->fresh())
            ->postJson("/api/chats/{$chat->id}/messages", ['message' => 'Sigo siendo el atleta aqui'])
            ->assertOk();

        $tipos = Message::where('chat_id', $chat->id)
            ->where('sender_id', $atleta->id)
            ->pluck('sender_type')
            ->unique()
            ->values()
            ->all();

        // Los dos mensajes del mismo hilo y de la misma persona tienen que
        // estar marcados igual: antes el segundo salia como 'trainer' y el hilo
        // quedaba partido en dos.
        $this->assertSame(['user'], $tipos);
    }
}
