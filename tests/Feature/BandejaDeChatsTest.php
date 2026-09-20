<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * La bandeja de conversaciones: que cuente bien y que no crezca en consultas.
 */
class BandejaDeChatsTest extends TestCase
{
    use RefreshDatabase;

    private function chatCon(User $atleta, int $mensajesSinLeer): Chat
    {
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);

        $trainer = Trainer::create([
            'user_id' => $entrenadorUser->id,
            'status' => 'approved',
            'name' => $entrenadorUser->name,
            'email' => $entrenadorUser->email,
            'phone' => '809-000-0000',
            'city_country' => 'Santo Domingo, RD',
            'sport_category' => 'Baloncesto',
            'experience' => '5 anos',
            'level_of_certification' => 'basica',
        ]);

        $chat = Chat::create([
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'status' => 'accepted',
        ]);

        // Mensajes del entrenador sin leer...
        for ($i = 0; $i < $mensajesSinLeer; $i++) {
            Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $entrenadorUser->id,
                'sender_type' => 'trainer',
                'message' => 'Mensaje ' . $i,
                'read' => false,
            ]);
        }

        // ...y uno propio, que nunca debe contar como no leido.
        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $atleta->id,
            'sender_type' => 'user',
            'message' => 'Mio, no cuenta',
            'read' => false,
        ]);

        return $chat;
    }

    public function test_el_contador_de_no_leidos_es_correcto(): void
    {
        $atleta = User::factory()->create();
        $this->chatCon($atleta, 3);

        $this->actingAs($atleta)
            ->getJson('/api/chats')
            ->assertOk()
            ->assertJsonPath('0.unread_count', 3);
    }

    /**
     * Cuenta las consultas que hace la bandeja para un numero dado de chats.
     */
    private function consultasDeLaBandeja(int $cuantosChats): int
    {
        $atleta = User::factory()->create();

        foreach (range(1, $cuantosChats) as $i) {
            $this->chatCon($atleta, 2);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($atleta)
            ->getJson('/api/chats')
            ->assertOk()
            ->assertJsonCount($cuantosChats);

        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    }

    public function test_la_bandeja_no_hace_una_consulta_por_conversacion(): void
    {
        // Lo que importa no es el numero absoluto -depende de cuantas
        // relaciones se carguen- sino si CRECE con el numero de conversaciones.
        // El contador de no leidos se resolvia dentro del bucle, asi que cada
        // chat de mas anadia una consulta de mas.
        $conDos = $this->consultasDeLaBandeja(2);
        $conSeis = $this->consultasDeLaBandeja(6);

        $this->assertSame(
            $conDos,
            $conSeis,
            "La bandeja hizo {$conDos} consultas con 2 conversaciones y {$conSeis} con 6: "
                . 'el coste crece con el numero de chats, hay un N+1.'
        );
    }
}
