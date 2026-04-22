<?php

namespace App\Mail;

use App\Models\Colaborador;
use App\Models\Empresa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaColaboradorMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $nombreCompleto;

    public string $empresaNombre;

    public string $nombreApp;

    public ?string $logoUrl;

    public string $linkDescarga;

    public string $supportEmail;

    public function __construct(
        public Colaborador $colaborador,
        public Empresa $empresa
    ) {
        $this->nombreCompleto = $colaborador->nombre_completo;
        $this->empresaNombre = $empresa->nombre;
        $this->nombreApp = $empresa->nombre_app ?: 'Paco';
        $this->logoUrl = $empresa->logo_url;
        $this->linkDescarga = $empresa->link_descarga_app ?: config('paco.app_download_link');
        $this->supportEmail = config('paco.support_email');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Bienvenido/a a {$this->nombreApp}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-colaborador',
        );
    }
}
