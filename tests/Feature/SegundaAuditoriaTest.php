<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Controles introducidos en la segunda ronda de auditoria.
 *
 * Un test por hallazgo, con el identificador en el nombre del metodo, igual
 * que en AutorizacionTest.
 */
class SegundaAuditoriaTest extends TestCase
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

    // ---------------------------------------------------------------- AU-1

    public function test_las_estadisticas_son_solo_del_propio_perfil(): void
    {
        $propio = User::factory()->create();
        $ajeno = User::factory()->create();

        $this->actingAs($propio)
            ->getJson("/api/user-stats/{$propio->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        // Recorrer los ids de los demas era gratis: son consecutivos.
        $this->actingAs($propio)
            ->getJson("/api/user-stats/{$ajeno->id}")
            ->assertStatus(403);
    }

    public function test_un_admin_si_puede_ver_las_estadisticas_de_otro(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $otro = User::factory()->create();

        $this->actingAs($admin)
            ->getJson("/api/user-stats/{$otro->id}")
            ->assertOk();
    }

    // ---------------------------------------------------------------- AU-2

    public function test_las_solicitudes_no_exponen_mas_datos_del_solicitante_de_los_necesarios(): void
    {
        $atleta = User::factory()->create([
            'email' => 'atleta@ejemplo.com',
            'phone' => '809-555-0000',
            'birthdate' => '1990-05-05',
            'bio' => 'Biografia privada del atleta',
        ]);

        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $trainer = $this->entrenador($entrenadorUser);

        Training::create([
            'user_id' => $atleta->id,
            'trainer_id' => $trainer->id,
            'sport_level' => 'Principiante',
            'description' => 'Quiero entrenar',
            'status' => 'pending',
        ]);

        $respuesta = $this->actingAs($entrenadorUser)
            ->getJson("/api/training?trainer_id={$trainer->id}")
            ->assertOk();

        $solicitante = $respuesta->json('0.user');

        // Lo que el entrenador necesita para coordinar sigue estando: sin esto
        // la pantalla de solicitudes muestra "Desconocido" en el contacto.
        $this->assertSame('atleta@ejemplo.com', $solicitante['email']);
        $this->assertSame('809-555-0000', $solicitante['phone']);
        $this->assertArrayHasKey('name', $solicitante);

        // Lo que no hace falta para nada ya no viaja.
        $this->assertArrayNotHasKey('birthdate', $solicitante);
        $this->assertArrayNotHasKey('bio', $solicitante);
        $this->assertArrayNotHasKey('email_verified_at', $solicitante);
    }

    // ---------------------------------------------------------------- R-20

    public function test_aprobar_o_rechazar_una_ficha_no_degrada_a_un_administrador(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::factory()->create(['user_type' => 'admin']);

        // Nada impide que un administrador mande su propia solicitud.
        $ficha = $this->entrenador($admin);
        $ficha->status = 'pending';
        $ficha->save();

        $this->actingAs($admin)
            ->putJson("/api/update-status/{$ficha->id}", ['status' => 'approved'])
            ->assertOk();

        $this->assertSame('admin', $admin->fresh()->user_type);

        $this->actingAs($admin)
            ->putJson("/api/update-status/{$ficha->id}", ['status' => 'rejected'])
            ->assertOk();

        $this->assertSame('admin', $admin->fresh()->user_type);
    }

    public function test_rechazar_la_ficha_de_un_entrenador_si_lo_devuelve_a_usuario(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::factory()->create(['user_type' => 'admin']);
        $entrenadorUser = User::factory()->create(['user_type' => 'entrenador']);
        $ficha = $this->entrenador($entrenadorUser);

        $this->actingAs($admin)
            ->putJson("/api/update-status/{$ficha->id}", ['status' => 'rejected'])
            ->assertOk();

        $this->assertSame('user', $entrenadorUser->fresh()->user_type);
    }
}
