<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Las acciones de administracion estan protegidas EN LA RUTA.
 *
 * La comprobacion de rol vivia dentro de cada metodo, repetida 29 veces. Se
 * movio al middleware 'admin' y se quitaron de los controladores, asi que a
 * partir de ahora la unica barrera de estas rutas es el middleware: si alguien
 * lo borra al reorganizar el archivo de rutas, no queda NADA debajo.
 *
 * Por eso esta prueba mira la tabla de rutas y no solo el comportamiento. Un
 * barrido funcional pasaria en verde si la ruta dejara de existir o cambiara de
 * URI; esto falla y dice cual falta.
 */
class RutasDeAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Las 16 rutas de las que se retiro la comprobacion del controlador.
     */
    public static function rutasDeAdmin(): array
    {
        return [
            'crear evento' => ['POST', 'api/calendar'],
            'editar evento' => ['PUT', 'api/calendar/{calendar}'],
            'borrar evento' => ['DELETE', 'api/calendar/{calendar}'],
            'importar calendario' => ['POST', 'api/scrap-calendar'],
            'crear producto' => ['POST', 'api/products'],
            'editar producto' => ['PUT', 'api/products/{id}'],
            'borrar producto' => ['DELETE', 'api/products/{id}'],
            'editar noticia' => ['PUT', 'api/news/{id}'],
            'borrar noticia' => ['DELETE', 'api/news/{id}'],
            'listado de solicitudes de entrenador' => ['GET', 'api/trainer'],
            'todas las solicitudes de entrenador' => ['GET', 'api/trainer-requests'],
            'aprobar o rechazar entrenador' => ['PUT', 'api/update-status/{id}'],
            'ver ajuste del catalogo' => ['GET', 'api/config/{config}'],
            'crear ajuste del catalogo' => ['POST', 'api/config'],
            'editar ajuste del catalogo' => ['PUT', 'api/config/{config}'],
            'borrar ajuste del catalogo' => ['DELETE', 'api/config/{config}'],
        ];
    }

    /**
     * @dataProvider rutasDeAdmin
     */
    public function test_la_ruta_lleva_el_middleware_de_admin(string $metodo, string $uri): void
    {
        $ruta = collect(Route::getRoutes())->first(
            fn ($r) => $r->uri() === $uri && in_array($metodo, $r->methods(), true)
        );

        $this->assertNotNull($ruta, "No existe la ruta {$metodo} /{$uri}.");

        // gatherMiddleware() devuelve lo que hay escrito en la ruta, con los
        // alias SIN resolver: alli pone 'admin', no la clase. Se aceptan las
        // dos formas para que la prueba no dependa de como se haya aplicado.
        $middleware = $ruta->gatherMiddleware();
        $protegida = in_array('admin', $middleware, true)
            || in_array(EnsureUserIsAdmin::class, $middleware, true);

        $this->assertTrue(
            $protegida,
            "La ruta {$metodo} /{$uri} se quedo sin el middleware 'admin', y el "
                . 'controlador ya no comprueba el rol: ahora mismo la puede usar cualquiera.'
        );
    }

    /**
     * El catalogo de ajustes se LEE sin ser admin: cada usuario necesita la
     * lista para elegir sus preferencias. Si esta ruta acabara dentro del grupo
     * 'admin' por arrastre, se romperia la pantalla de ajustes de todos.
     */
    public function test_el_listado_del_catalogo_sigue_abierto_a_cualquier_usuario(): void
    {
        $normal = User::factory()->create(['user_type' => 'user']);

        $this->actingAs($normal)->getJson('/api/config')->assertOk();
    }

    public function test_un_usuario_normal_no_pasa_y_un_admin_si(): void
    {
        $normal = User::factory()->create(['user_type' => 'user']);
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($normal)
            ->postJson('/api/config', ['configuration' => 'tema'])
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/config', ['configuration' => 'tema'])
            ->assertStatus(201);
    }

    public function test_sin_sesion_responde_401_y_no_403(): void
    {
        // El middleware de admin va DETRAS del de autenticacion: a quien no se
        // ha identificado hay que decirle que se identifique, no que no le
        // toca. Si el orden se invirtiera, esta ruta devolveria 403.
        $this->postJson('/api/config', ['configuration' => 'tema'])
            ->assertStatus(401);
    }
}
