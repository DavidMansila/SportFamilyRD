<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $Admin;
    public $usuario;

    public function __construct($Admin, $usuario)
    {
        $this->Admin = $Admin;
        $this->usuario = $usuario;
    }

    public function build()
    {
        return $this->subject('Nueva solicitud de ser entrenador recibida')
            ->view('emails.nueva_solicitud_de_ser_entrenador');
    }
}