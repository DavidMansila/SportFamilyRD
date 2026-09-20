<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * El correo de verificacion: que se mande de verdad, y que cuando NO se pueda
 * mandar se note.
 *
 * El fallo que motiva estas pruebas fue justo ese silencio: la aplicacion
 * respondia "¡Correo de verificación enviado!" con un 200 mientras el correo
 * no salia de ninguna parte (en Render el plan gratuito bloquea los puertos
 * SMTP, y sin MAIL_MAILER en el entorno Laravel cae en el driver 'log', que
 * escribe el mensaje en un fichero y no lanza ningun error). Nada en la
 * pantalla ni en los logs decia que el correo no habia salido.
 */
class CorreoVerificacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Deja el mailer apuntando a un puerto donde no escucha nadie, para
     * provocar un fallo de transporte real sin depender de la red.
     */
    private function romperElEnvioDeCorreo(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);
    }

    public function test_el_correo_de_verificacion_lleva_la_plantilla_propia_y_el_enlace_firmado(): void
    {
        $usuario = User::factory()->unverified()->create(['name' => 'David']);

        $this->actingAs($usuario)
            ->postJson('/api/email/verification-notification')
            ->assertOk();

        // El transporte 'array' (phpunit.xml) guarda los mensajes en memoria.
        $mensajes = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $mensajes);

        // getMessage() devuelve el MIME ya serializado; el objeto Email, con el
        // cuerpo separado en HTML y texto, es getOriginalMessage().
        $correo = $mensajes[0]->getOriginalMessage();
        $html = $correo->getHtmlBody();

        $this->assertStringContainsString('Confirma tu correo', $correo->getSubject());
        $this->assertStringContainsString('Verificar mi cuenta', $html);
        $this->assertStringContainsString('David', $html);

        // El enlace del boton tiene que ser el firmado de la API, con firma y
        // caducidad: es lo unico que permite verificar la cuenta.
        $this->assertStringContainsString('/api/email/verify/' . $usuario->id . '/', $html);
        $this->assertStringContainsString('signature=', $html);
        $this->assertStringContainsString('expires=', $html);

        // Y la parte en texto plano tiene que viajar tambien (multipart).
        $this->assertStringContainsString('El enlace caduca en', $correo->getTextBody());
    }

    public function test_el_logo_incrustado_va_con_nombre_de_fichero_y_extension(): void
    {
        $usuario = User::factory()->unverified()->create();

        $this->actingAs($usuario)
            ->postJson('/api/email/verification-notification')
            ->assertOk();

        $adjuntos = Mail::mailer()->getSymfonyTransport()->messages()[0]
            ->getOriginalMessage()
            ->getAttachments();

        $this->assertCount(1, $adjuntos);

        // El nombre importa: pasandole una ruta a embed(), Laravel bautiza el
        // adjunto con Str::random(10), un nombre sin extension. Por SMTP da
        // igual, pero la API de Brevo mira la extension para decidir si acepta
        // el fichero y respondia "Unsupported file format: <nombre>" (400),
        // tirando el correo entero. Con un nombre normal, se acepta.
        $this->assertSame('logo.png', $adjuntos[0]->getFilename());
        $this->assertSame('image/png', $adjuntos[0]->getMediaType() . '/' . $adjuntos[0]->getMediaSubtype());
    }

    public function test_si_el_envio_falla_el_reenvio_lo_dice_en_vez_de_responder_ok(): void
    {
        $usuario = User::factory()->unverified()->create();
        $this->romperElEnvioDeCorreo();

        $this->actingAs($usuario)
            ->postJson('/api/email/verification-notification')
            ->assertStatus(503);
    }

    public function test_el_registro_se_completa_aunque_el_correo_no_salga(): void
    {
        $this->romperElEnvioDeCorreo();

        $respuesta = $this->postJson('/api/user', [
            'name' => 'Nuevo',
            'email' => 'nuevo@ejemplo.com',
            'password' => 'contrasena8',
            'password_confirmation' => 'contrasena8',
        ]);

        // La cuenta se crea igual: antes la excepcion del envio se colaba en el
        // catch del controlador y devolvia 500 "No se pudo crear el usuario",
        // con el usuario YA guardado en la base de datos. Al reintentar, el
        // registro fallaba por correo duplicado.
        $respuesta->assertStatus(201)
            ->assertJsonPath('verification_email_sent', false);

        $this->assertDatabaseHas('users', ['email' => 'nuevo@ejemplo.com']);
    }
}
