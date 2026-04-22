<?php

namespace App\Services;

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\MensajeCandidato;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CandidatoEstatusService
{
    /**
     * BL: RN-05 — Cambiar estatus de un candidato. No se permite repetir un estatus ya registrado.
     *
     * @throws \InvalidArgumentException si el estatus ya existe en el historial
     */
    public function cambiarEstatus(
        CandidatoReclutamiento $candidato,
        string $nuevoEstatus,
        User $usuario,
        string $comentario,
    ): void {
        if ($candidato->tieneEstatus($nuevoEstatus)) {
            throw new \InvalidArgumentException(
                "El estatus '{$nuevoEstatus}' ya existe en el historial de este candidato."
            );
        }

        DB::transaction(function () use ($candidato, $nuevoEstatus, $usuario, $comentario): void {
            HistorialEstatusCandidato::cerrarActual($candidato);

            HistorialEstatusCandidato::create([
                'candidato_id' => $candidato->id,
                'estatus' => $nuevoEstatus,
                'creado_por' => $usuario->id,
                'fecha_inicio' => now(),
            ]);

            $candidato->update(['estatus' => $nuevoEstatus]);

            MensajeCandidato::create([
                'candidato_id' => $candidato->id,
                'user_id' => $usuario->id,
                'comentario' => $comentario,
            ]);
        });
    }

    public function agregarComentario(
        CandidatoReclutamiento $candidato,
        User $usuario,
        string $comentario,
    ): MensajeCandidato {
        return MensajeCandidato::create([
            'candidato_id' => $candidato->id,
            'user_id' => $usuario->id,
            'comentario' => $comentario,
        ]);
    }
}
