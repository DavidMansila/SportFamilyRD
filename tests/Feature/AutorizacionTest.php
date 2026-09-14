<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Configuration;
use App\Models\Post;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprobaciones de autorizacion introducidas durante la auditoria.
 *
 * Cada prueba fija UN control concreto para que no se pueda perder en una
 * refactorizacion futura sin que la suite lo diga. El nombre de cada metodo
 * lleva el identificador del hallazgo al que corresponde.
 */
class AutorizacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $tipo = 'user'): User
    {
        return User::factory()->create(['user_type' => $tipo]);
    }

    private function entrenador(User $user, string $status = 'approved'): Trainer
    {
        return Trainer::create([
            'user_id' => $user->id,
            'status' => $status,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '809-000-0000',
            'city_country' => 'Santo Domingo, RD',
            'sport_category' => 'Baloncesto',
            'experience' => '5 años',
            'level_of_certification' => 'basica',
        ]);
    }

    // ---------------------------------------------------------------- C-2

    public function test_escribir_en_el_calendario_por_scraping_exige_admin(): void
    {
        $evento = [[
            'Title' => 'Clásico LIDOM',
            'date' => '2026-11-15',
            'price' => 500,
        ]];

        // Sin sesion.
        $this->postJson('/api/scrap-calendar', ['events' => $evento])
            ->assertStatus(401);

        // Usuario normal.
        $this->actingAs($this->usuario())
            ->postJson('/api/scrap-calendar', ['events' => $evento])
            ->assertStatus(403);

        // Admin.
        $this->actingAs($this->usuario('admin'))
            ->postJson('/api/scrap-calendar', ['events' => $evento])
            ->assertStatus(201);

        $this->assertDatabaseHas('calendars', ['Title' => 'Clásico LIDOM']);
    }

    public function test_el_scraping_de_calendario_acota_el_tamano_del_lote(): void
    {
        $muchos = array_fill(0, 201, ['Title' => 'X', 'date' => '2026-01-01']);

        $this->actingAs($this->usuario('admin'))
            ->postJson('/api/scrap-calendar', ['events' => $muchos])
            ->assertStatus(422);
    }

    // ---------------------------------------------------------------- C-3

    public function test_no_se_puede_verificar_un_correo_sin_la_firma(): void
    {
        $victima = User::factory()->unverified()->create(['email' => 'victima@ejemplo.com']);

        // El ataque original: sha1 del correo (calculable por cualquiera) y el
        // usuario elegido con ?user_id=, sin firma ninguna.
        $hash = sha1('victima@ejemplo.com');

        $this->get("/api/email/verify/1/{$hash}?user_id={$victima->id}")
            ->assertStatus(403);

        $this->assertNull($victima->fresh()->email_verified_at);
    }

    public function test_el_enlace_firmado_si_verifica_el_correo(): void
    {
        $user = User::factory()->unverified()->create();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->get($url)->assertRedirect();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_un_enlace_firmado_para_otro_usuario_no_sirve(): void
    {
        $a = User::factory()->unverified()->create();
        $b = User::factory()->unverified()->create();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $a->id, 'hash' => sha1($a->getEmailForVerification())]
        );

        // Cambiar el id invalida la firma.
        $manipulada = str_replace("/verify/{$a->id}/", "/verify/{$b->id}/", $url);

        $this->get($manipulada)->assertStatus(403);
        $this->assertNull($b->fresh()->email_verified_at);
    }

    public function test_reenviar_la_verificacion_no_acepta_un_user_id_del_cliente(): void
    {
        $victima = User::factory()->unverified()->create();

        // Sin sesion no se puede pedir el reenvio a nombre de nadie.
        $this->postJson('/api/email/verification-notification', ['user_id' => $victima->id])
            ->assertStatus(401);

        // Con sesion, el user_id del cuerpo se ignora: el correo sale para quien
        // esta autenticado, no para quien diga el cliente.
        $otro = User::factory()->unverified()->create();

        \Illuminate\Support\Facades\Notification::fake();

        $this->actingAs($otro)
            ->postJson('/api/email/verification-notification', ['user_id' => $victima->id])
            ->assertOk();

        \Illuminate\Support\Facades\Notification::assertSentTo($otro, \App\Notifications\VerifyEmail::class);
        \Illuminate\Support\Facades\Notification::assertNotSentTo($victima, \App\Notifications\VerifyEmail::class);
    }

    // ---------------------------------------------------------------- A-3

    public function test_el_foro_publico_no_expone_datos_personales(): void
    {
        $autor = User::factory()->create([
            'email' => 'privado@ejemplo.com',
            'phone' => '809-555-1234',
            'birthdate' => '1990-01-01',
        ]);

        Post::create([
            'titulo' => 'Hola',
            'contenido' => 'Contenido',
            'categoria' => 'general',
            'user_id' => $autor->id,
        ]);

        $respuesta = $this->getJson('/api/post')->assertOk();
        $cuerpo = $respuesta->getContent();

        $this->assertStringNotContainsString('privado@ejemplo.com', $cuerpo);
        $this->assertStringNotContainsString('809-555-1234', $cuerpo);
        $this->assertStringNotContainsString('1990-01-01', $cuerpo);

        // Lo que el frontend SI necesita sigue estando.
        $respuesta->assertJsonPath('posts.0.user.name', $autor->name);
    }

    // ---------------------------------------------------------------- A-4

    public function test_gestionar_la_configuracion_global_exige_admin(): void
    {
        $normal = $this->usuario();
        $config = Configuration::create(['configuration' => 'notificaciones']);

        $this->actingAs($normal)
            ->postJson('/api/config', ['configuration' => 'nueva'])
            ->assertStatus(403);

        $this->actingAs($normal)
            ->deleteJson("/api/config/{$config->id}")
            ->assertStatus(403);

        $this->actingAs($this->usuario('admin'))
            ->deleteJson("/api/config/{$config->id}")
            ->assertStatus(204);
    }

    // ---------------------------------------------------------------- A-8

    public function test_cambiar_la_contrasena_revoca_las_sesiones_anteriores(): void
    {
        $user = User::factory()->create(['password' => bcrypt('claveVieja1')]);
        $tokenViejo = $user->createToken('viejo')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenViejo}")
            ->postJson('/api/change-password', [
                'current_password' => 'claveVieja1',
                'new_password' => 'claveNueva123',
            ])
            ->assertOk()
            ->assertJsonStructure(['token']);

        // El token anterior ya no vale.
        $this->assertSame(0, $user->fresh()->tokens()->where('name', 'viejo')->count());
    }

    // ---------------------------------------------------------------- M-1

    public function test_no_se_puede_leer_la_solicitud_de_entrenamiento_de_otro(): void
    {
        $solicitante = $this->usuario();
        $entrenadorUser = $this->usuario('entrenador');
        $trainer = $this->entrenador($entrenadorUser);
        $ajeno = $this->usuario();

        $training = Training::create([
            'user_id' => $solicitante->id,
            'trainer_id' => $trainer->id,
            'sport_level' => 'Principiante',
            'description' => 'Quiero mejorar mi tiro',
            'status' => 'pending',
        ]);

        $this->actingAs($ajeno)
            ->getJson("/api/training/{$training->id}")
            ->assertStatus(403);

        // El solicitante y el entrenador destinatario si pueden.
        $this->actingAs($solicitante)->getJson("/api/training/{$training->id}")->assertOk();
        $this->actingAs($entrenadorUser)->getJson("/api/training/{$training->id}")->assertOk();
    }

    // ---------------------------------------------------------------- M-5

    public function test_el_carrito_rechaza_articulos_inexistentes_y_cantidades_absurdas(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => 999999,
                'quantity' => 1,
            ])
            ->assertStatus(422);

        $this->actingAs($user)
            ->postJson('/api/cart/items', [
                'item_type' => 'product',
                'item_id' => 1,
                'quantity' => 100000,
            ])
            ->assertStatus(422);
    }

    // ---------------------------------------------------------------- chats

    public function test_no_se_puede_leer_el_chat_de_otras_personas(): void
    {
        $dueno = $this->usuario();
        $entrenadorUser = $this->usuario('entrenador');
        $trainer = $this->entrenador($entrenadorUser);
        $ajeno = $this->usuario();

        $chat = Chat::create([
            'user_id' => $dueno->id,
            'trainer_id' => $trainer->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($ajeno)->getJson("/api/chats/{$chat->id}")->assertStatus(403);
        $this->actingAs($ajeno)
            ->postJson("/api/chats/{$chat->id}/messages", ['message' => 'hola'])
            ->assertStatus(403);

        $this->actingAs($dueno)->getJson("/api/chats/{$chat->id}")->assertOk();
    }

    public function test_los_mensajes_de_chat_tienen_limite_de_longitud(): void
    {
        $dueno = $this->usuario();
        $trainer = $this->entrenador($this->usuario('entrenador'));

        $chat = Chat::create([
            'user_id' => $dueno->id,
            'trainer_id' => $trainer->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($dueno)
            ->postJson("/api/chats/{$chat->id}/messages", ['message' => str_repeat('a', 1001)])
            ->assertStatus(422);
    }
}
