<?php

namespace App\Services\Correos;

use App\Mail\BienvenidaColaboradorMail;
use App\Models\Colaborador;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CorreoBienvenidaService
{
    /**
     * BL: Envía correo de bienvenida al colaborador si tiene email real y la empresa está activa.
     * El fallo del envío no debe afectar la creación del colaborador.
     */
    public function enviar(Colaborador $colaborador): bool
    {
        $colaborador->loadMissing('empresa');
        $empresa = $colaborador->empresa;

        if ($empresa === null) {
            Log::warning('CorreoBienvenida: colaborador sin empresa', ['colaborador_id' => $colaborador->id]);

            return false;
        }

        $email = $colaborador->email;
        if ($email === null || $email === '' || $this->esEmailPlaceholder($email)) {
            Log::info('CorreoBienvenida: omitido (email placeholder o vacío)', [
                'colaborador_id' => $colaborador->id,
            ]);

            return false;
        }

        if (! $empresa->activo) {
            Log::info('CorreoBienvenida: omitido (empresa inactiva)', [
                'colaborador_id' => $colaborador->id,
                'empresa_id' => $empresa->id,
            ]);

            return false;
        }

        try {
            Mail::to($email)->send(new BienvenidaColaboradorMail($colaborador, $empresa));

            Log::info('CorreoBienvenida: enviado', [
                'colaborador_id' => $colaborador->id,
                'email' => $email,
                'empresa_id' => $empresa->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('CorreoBienvenida: error al enviar', [
                'colaborador_id' => $colaborador->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function esEmailPlaceholder(string $email): bool
    {
        return str_ends_with($email, '@sin-email.tecben.local');
    }
}
