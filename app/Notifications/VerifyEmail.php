<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
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
}
