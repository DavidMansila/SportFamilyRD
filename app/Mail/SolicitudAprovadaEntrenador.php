<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobadaEntrenador extends Mailable
{
    use Queueable, SerializesModels;

    public $entrenador;

    public function __construct($entrenador)
    {
        $this->entrenador = $entrenador;
    }

    public function build()
    {
        return $this->subject('¡Tu solicitud de ser entrenador fue aprobada!')
            ->view('emails.solicitud_aprobada_entrenador');
    }
}