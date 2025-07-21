<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudExpirada extends Mailable
{
    use Queueable, SerializesModels;
    
    public $entrenador;
    public $usuario;
    /**
     * Create a new message instance.
     */
    public function __construct($entrenador, $usuario)
    {
        //
        $this->entrenador = $entrenador;
        $this->usuario = $usuario;
    }

    public function build()
    {
        return $this->subject('Nueva solicitud de entrenamiento expirada')
            ->view('emails.nueva_solicitud_expirada');
    }
}
