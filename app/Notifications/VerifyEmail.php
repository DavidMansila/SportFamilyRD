<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Enlace firmado de verificacion.
     *
     * Antes esto firmaba la ruta WEB 'verification.verify' y despues reescribia
     * el path con str_replace('/email/verify/', '/api/email/verify/'): la firma
     * se calcula sobre la URL completa, asi que cambiarla despues de firmarla la
     * invalidaba. La firma nunca fue valida -y como la ruta destino tampoco
     * llevaba el middleware 'signed', tampoco se comprobaba nunca.
     *
     * Ahora se firma directamente la ruta de la API, que es la que atiende el
     * enlace, y no se toca la URL despues.
     *
     * Tampoco se manda ya 'user_id': el usuario se resuelve por el {id} de la
     * ruta. Iba como parametro extra y el endpoint lo prefería sobre el {id},
     * lo que permitia verificar la cuenta de otra persona.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Cuerpo del correo.
     *
     * Antes se usaba el de Laravel: la plantilla generica en ingles ("Verify
     * Email Address" / "If you did not create an account...") con el logo de
     * Laravel de fabrica. Ahora usa la plantilla propia, en español y con la
     * marca, y manda tambien la parte en texto plano.
     */
    public function toMail($notifiable)
    {
        $appName = config('app.name', 'SportFamilyRD');

        $datos = [
            'appName' => $appName,
            'nombre' => $notifiable->name ?: 'atleta',
            'url' => $this->verificationUrl($notifiable),
            'expiraMinutos' => config('auth.verification.expire', 60),
            // El logo se adjunta desde disco (la vista lo incrusta con
            // $message->embed), no se enlaza: los clientes de correo bloquean
            // las imagenes remotas por defecto.
            'logoPath' => public_path('imagenes/Logo2.png'),
        ];

        return (new MailMessage)
            ->subject('Confirma tu correo · ' . $appName)
            ->view(
                ['emails.verificar-correo', 'emails.verificar-correo-texto'],
                $datos
            );
    }
}
