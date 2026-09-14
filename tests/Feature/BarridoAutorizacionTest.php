<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Comment;
use App\Models\News;
use App\Models\Post;
use App\Models\Product;
use App\Models\Reply;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Barridos completos que pide el protocolo de pase a produccion (Entregable 3):
 *
 *   - IDOR: por cada recurso con {id}, una cuenta ajena recibe 403.
 *   - Escalada de privilegios: una cuenta normal no puede ejecutar ninguna
 *     accion de administracion.
 *
 * Son barridos y no casos sueltos a proposito: la lista de rutas crece, y lo
 * que hay que fijar es que NINGUNA quede sin comprobacion de propiedad o de rol.
 */
class BarridoAutorizacionTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $tipo = 'user'): User
    {
        return User::factory()->create(['user_type' => $tipo]);
    }

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

    // ================================================================= IDOR

    /**
     * Cada caso: [descripcion, metodo, url, cuerpo]
     * Se ejecuta con una cuenta que NO es dueña del recurso y debe dar 403.
     */
    public function test_barrido_idor_ningun_recurso_ajeno_es_accesible(): void
    {
        $dueno = $this->usuario();
        $ajeno = $this->usuario();
        $entrenadorUser = $this->usuario('entrenador');
        $trainer = $this->entrenador($entrenadorUser);

        $post = Post::create([
            'titulo' => 'Post del dueño',
            'contenido' => 'contenido',
            'categoria' => 'general',
            'user_id' => $dueno->id,
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $dueno->id,
            'texto' => 'comentario del dueño',
        ]);

        $reply = Reply::create([
            'comment_id' => $comment->id,
            'user_id' => $dueno->id,
            'texto' => 'respuesta del dueño',
        ]);

        $producto = Product::create([
            'name' => 'Balón',
            'description' => 'x',
            'price' => 10,
            'category' => 'x',
            'image' => 'https://ejemplo.test/b.jpg',
            'stock' => 5,
        ]);

        $cart = Cart::create(['user_id' => $dueno->id, 'status' => 'active']);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'item_type' => 'product',
            'item_id' => $producto->id,
            'quantity' => 1,
        ]);

        $casos = [
            'editar post ajeno'          => ['putJson',    "/api/post/{$post->id}", ['titulo' => 'secuestrado']],
            'borrar post ajeno'          => ['deleteJson', "/api/post/{$post->id}", []],
            'editar comentario ajeno'    => ['putJson',    "/api/post/update-comment/{$comment->id}", ['texto' => 'x']],
            'borrar comentario ajeno'    => ['deleteJson', "/api/post/delete-comment/{$comment->id}", []],
            'editar respuesta ajena'     => ['putJson',    "/api/post/update-reply/{$reply->id}", ['texto' => 'x']],
            'borrar respuesta ajena'     => ['deleteJson', "/api/post/destroy-reply/{$reply->id}", []],
            'editar usuario ajeno'       => ['putJson',    "/api/user/{$dueno->id}", ['name' => 'x']],
            'borrar usuario ajeno'       => ['deleteJson', "/api/user/{$dueno->id}", []],
            'cambiar avatar ajeno'       => ['postJson',   "/api/user/{$dueno->id}/image", []],
            'modificar carrito ajeno'    => ['putJson',    "/api/cart/items/{$item->id}", ['quantity' => 9]],
            'vaciar item de carrito ajeno' => ['deleteJson', "/api/cart/items/{$item->id}", []],
            'editar solicitud entrenador ajena' => ['putJson', "/api/trainer/{$trainer->id}", ['name' => 'x']],
        ];

        foreach ($casos as $descripcion => [$metodo, $url, $cuerpo]) {
            $respuesta = $this->actingAs($ajeno)->{$metodo}($url, $cuerpo);

            $this->assertSame(
                403,
                $respuesta->getStatusCode(),
                "IDOR: '{$descripcion}' ({$url}) devolvio {$respuesta->getStatusCode()} en vez de 403"
            );
        }
    }

    public function test_el_dueno_si_puede_operar_sobre_lo_suyo(): void
    {
        // Contrapeso del barrido anterior: comprobar que los 403 no vienen de
        // haber roto la ruta para todo el mundo.
        $dueno = $this->usuario();

        $post = Post::create([
            'titulo' => 'Mi post',
            'contenido' => 'contenido',
            'categoria' => 'general',
            'user_id' => $dueno->id,
        ]);

        $this->actingAs($dueno)
            ->putJson("/api/post/{$post->id}", ['titulo' => 'Mi post editado'])
            ->assertOk();

        $this->actingAs($dueno)
            ->putJson("/api/user/{$dueno->id}", ['name' => 'Nombre nuevo'])
            ->assertOk();

        $this->assertSame('Mi post editado', $post->fresh()->titulo);
        $this->assertSame('Nombre nuevo', $dueno->fresh()->name);
    }

    // ================================================ ESCALADA DE PRIVILEGIOS

    public function test_barrido_escalada_ninguna_accion_de_admin_es_accesible(): void
    {
        $normal = $this->usuario();
        $entrenadorUser = $this->usuario('entrenador');
        $trainer = $this->entrenador($entrenadorUser);

        $producto = Product::create([
            'name' => 'Balón', 'description' => 'x', 'price' => 10,
            'category' => 'x', 'image' => 'https://ejemplo.test/b.jpg', 'stock' => 5,
        ]);

        $evento = Calendar::create([
            'Title' => 'Evento', 'date' => '2026-12-01', 'time' => '10:00:00',
            'place' => 'Santo Domingo', 'Description' => 'x', 'price' => 100, 'quantity' => 10,
        ]);

        $noticia = News::create([
            'title' => 'Noticia', 'description' => 'cuerpo', 'author' => 'a',
            'category' => 'futbol', 'published_at' => now(),
        ]);

        $casos = [
            'crear producto'      => ['postJson',   '/api/products', ['name' => 'x', 'description' => 'x', 'price' => 1, 'category' => 'x', 'image' => 'https://e.test/a.jpg', 'stock' => 1]],
            'editar producto'     => ['putJson',    "/api/products/{$producto->id}", ['name' => 'x']],
            'borrar producto'     => ['deleteJson', "/api/products/{$producto->id}", []],
            'crear evento'        => ['postJson',   '/api/calendar', ['Title' => 'x', 'date' => '2026-12-01', 'time' => '10:00', 'place' => 'x', 'price' => 1, 'quantity' => 1]],
            'editar evento'       => ['putJson',    "/api/calendar/{$evento->id}", ['Title' => 'x', 'date' => '2026-12-01', 'time' => '10:00', 'place' => 'x', 'price' => 1, 'quantity' => 1]],
            'borrar evento'       => ['deleteJson', "/api/calendar/{$evento->id}", []],
            'editar noticia'      => ['putJson',    "/api/news/{$noticia->id}", ['title' => 'x', 'description' => 'y', 'author' => 'a', 'category' => 'c', 'published_at' => '2026-01-01']],
            'borrar noticia'      => ['deleteJson', "/api/news/{$noticia->id}", []],
            'aprobar entrenador'  => ['putJson',    "/api/update-status/{$trainer->id}", ['status' => 'approved']],
            'listar solicitudes'  => ['getJson',    '/api/trainer', []],
            'listar solicitudes 2'=> ['getJson',    '/api/trainer-requests', []],
            'crear configuracion' => ['postJson',   '/api/config', ['configuration' => 'x']],
            'borrar configuracion'=> ['deleteJson', '/api/config/1', []],
            'escribir calendario por scraping' => ['postJson', '/api/scrap-calendar', ['events' => [['Title' => 'x', 'date' => '2026-12-01']]]],
        ];

        foreach ($casos as $descripcion => [$metodo, $url, $cuerpo]) {
            $respuesta = $this->actingAs($normal)->{$metodo}($url, $cuerpo);

            $this->assertSame(
                403,
                $respuesta->getStatusCode(),
                "Escalada: '{$descripcion}' ({$url}) devolvio {$respuesta->getStatusCode()} en vez de 403"
            );
        }
    }

    public function test_un_usuario_normal_no_puede_auto_aprobarse_como_entrenador(): void
    {
        $normal = $this->usuario();

        // status y user_id nunca se aceptan del cliente.
        $this->actingAs($normal)->postJson('/api/solicitud-entrenador', [
            'name' => 'Yo', 'email' => 'yo@ejemplo.test', 'phone' => '809-000-0000',
            'city_country' => 'SD', 'sport_category' => 'Tenis',
            'experience' => '1 año', 'level_of_certification' => 'basica',
            'status' => 'approved',
            'user_id' => 9999,
        ])->assertOk();

        $trainer = Trainer::where('user_id', $normal->id)->firstOrFail();

        $this->assertSame('pending', $trainer->status, 'La solicitud no debe nacer aprobada');
        $this->assertSame($normal->id, $trainer->user_id, 'user_id debe ser el del autenticado');
        $this->assertSame('user', $normal->fresh()->user_type, 'El tipo de cuenta no debe cambiar solo');
    }

    // ============================================================== ENLACES

    public function test_un_enlace_de_verificacion_caducado_no_sirve(): void
    {
        $user = User::factory()->unverified()->create();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Viajar mas alla de la caducidad del enlace.
        $this->travel(61)->minutes();

        $this->get($url)->assertStatus(403);
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
