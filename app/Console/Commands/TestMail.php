<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Diagnostico del envio de correo.
 *
 *   php artisan mail:test tucorreo@ejemplo.com
 *
 * Existe porque el fallo tipico del correo no lanza ningun error visible: con
 * MAIL_MAILER sin configurar, Laravel usa el driver 'log' y escribe el mensaje
 * en storage/logs en vez de mandarlo, asi que la aplicacion responde "enviado"
 * y todo parece correcto. Este comando imprime PRIMERO que transporte esta
 * activo y despues intenta un envio real, separando las dos preguntas: "¿esta
 * bien configurado?" y "¿el proveedor acepta el correo?".
 *
 * Con --plantilla manda el correo de verificacion de verdad (el de
 * App\Notifications\VerifyEmail), util para revisar como se ve el diseño en el
 * cliente de correo sin tener que registrar una cuenta nueva cada vez.
 */
class TestMail extends Command
{
    protected $signature = 'mail:test
                            {correo : Direccion de destino}
                            {--plantilla : Manda el correo de verificacion real en vez de uno de prueba}';

    protected $description = 'Comprueba la configuracion de correo y manda un mensaje de prueba';

    public function handle(): int
    {
        $correo = $this->argument('correo');
        $mailer = config('mail.default');

        $this->newLine();
        $this->line('<fg=gray>Configuracion activa</>');
        $this->table(['Ajuste', 'Valor'], [
            ['MAIL_MAILER (mail.default)', $mailer],
            ['MAIL_FROM_ADDRESS', config('mail.from.address') ?: '<sin definir>'],
            ['MAIL_FROM_NAME', config('mail.from.name') ?: '<sin definir>'],
            ['APP_URL', config('app.url')],
            ['APP_ENV', config('app.env')],
            ['BREVO_KEY', config('mail.mailers.brevo.key') ? 'definida (' . strlen(config('mail.mailers.brevo.key')) . ' caracteres)' : '<sin definir>'],
            ['SMTP host:puerto', config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port')],
        ]);

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn('MAIL_MAILER = "' . $mailer . '": no se manda nada de verdad.');
            $this->warn('El correo se escribe en storage/logs/laravel.log. Define MAIL_MAILER en el entorno.');
        }

        if ($mailer === 'smtp') {
            $this->warn('MAIL_MAILER = "smtp": en el plan gratuito de Render el trafico saliente a');
            $this->warn('los puertos 25, 465 y 587 esta bloqueado, asi que la conexion caduca sin enviar.');
        }

        $this->newLine();
        $this->line('Enviando a <fg=cyan>' . $correo . '</>...');

        try {
            if ($this->option('plantilla')) {
                // Usuario en memoria, sin guardar: solo hace falta para que la
                // notificacion tenga a quien saludar y a quien firmar el enlace.
                $usuario = new User([
                    'name' => 'Prueba',
                    'email' => $correo,
                ]);
                $usuario->id = 0;

                $usuario->notify(new VerifyEmail);
            } else {
                Mail::raw(
                    "Prueba de envio de SportFamilyRD.\n\nSi lees esto, el transporte de correo funciona.",
                    fn ($mensaje) => $mensaje->to($correo)->subject('Prueba de correo - SportFamilyRD')
                );
            }
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Fallo el envio: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Envio aceptado por el transporte "' . $mailer . '".');

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->line('<fg=yellow>Recuerda: con este transporte el correo NO sale de la aplicacion.</>');
        }

        return self::SUCCESS;
    }
}
