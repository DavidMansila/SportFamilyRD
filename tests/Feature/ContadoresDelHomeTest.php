<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los contadores de la portada.
 *
 * El numero de usuarios cuenta solo cuentas VERIFICADAS: una cuenta sin
 * verificar es una direccion que nadie ha confirmado -un registro a medias, una
 * prueba, un alta automatizada- y el numero de la portada tiene que significar
 * algo.
 *
 * El mismo filtro vive tambien en broadcast_user_count_change(), la funcion que
 * avisa por realtime cuando cambia la cuenta (database/migrations/
 * 2026_09_20_000200_trigger_contador_de_usuarios_en_vivo.php). Los dos tienen
 * que decir lo mismo: si se separan, el contador cambia de valor al refrescarse
 * en vivo y vuelve a cambiar al recargar la pagina.
 */
class ContadoresDelHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_contador_de_usuarios_solo_cuenta_los_verificados(): void
    {
        User::factory()->count(3)->create(['email_verified_at' => now()]);
        User::factory()->count(2)->create(['email_verified_at' => null]);

        $this->getJson('/api/home-stats?fresh=1')
            ->assertOk()
            ->assertJsonPath('users', 3);
    }

    public function test_verificar_una_cuenta_sube_el_contador(): void
    {
        $sinVerificar = User::factory()->create(['email_verified_at' => null]);

        $this->getJson('/api/home-stats?fresh=1')->assertJsonPath('users', 0);

        // Es el momento en el que el numero cambia de verdad: no al registrarse
        // -la cuenta nace sin verificar- sino al confirmar el correo. Por eso el
        // trigger de la base escucha tambien el UPDATE de email_verified_at, no
        // solo INSERT y DELETE.
        $sinVerificar->forceFill(['email_verified_at' => now()])->save();

        $this->getJson('/api/home-stats?fresh=1')->assertJsonPath('users', 1);
    }
}
