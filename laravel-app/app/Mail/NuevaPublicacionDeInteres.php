<?php

namespace App\Mail;

use App\Models\Publicacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaPublicacionDeInteres extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Publicacion $publicacion, public array $palabrasCoincidentes)
    {
    }

    public function build(): self
    {
        return $this->subject('Nuevo contenido de tu interés en INIFAP')
            ->view('emails.nueva_publicacion_interes');
    }
}
