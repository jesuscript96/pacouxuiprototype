<?php

namespace App\Observers;

use App\Models\CandidatoReclutamiento;
use Illuminate\Support\Facades\Log;

/**
 * BL: Detecta creación/actualización de candidatos para despachar
 * consulta de historial laboral IMSS vía Palenca.
 *
 * Legacy: app/Observers/RecruitmentCandidatesObserver.php
 */
class CandidatoReclutamientoObserver
{
    public function created(CandidatoReclutamiento $candidato): void
    {
        $this->verificarYDespacharHistorialLaboral($candidato, 'created');
    }

    public function updated(CandidatoReclutamiento $candidato): void
    {
        if ($candidato->wasChanged('curp')) {
            $this->verificarYDespacharHistorialLaboral($candidato, 'updated');
        }
    }

    private function verificarYDespacharHistorialLaboral(
        CandidatoReclutamiento $candidato,
        string $evento,
    ): void {
        $curp = $candidato->curp;

        if (empty($curp) || strlen($curp) !== 18) {
            return;
        }

        if ($candidato->historialLaboral()->exists()) {
            Log::info('CandidatoReclutamientoObserver: Historial laboral ya existe', [
                'candidato_id' => $candidato->id,
                'curp' => $curp,
            ]);

            return;
        }

        Log::info('CandidatoReclutamientoObserver: Despachando consulta de historial laboral', [
            'candidato_id' => $candidato->id,
            'curp' => $curp,
            'evento' => $evento,
        ]);

        \App\Jobs\ConsultarHistorialLaboralJob::dispatch($candidato);
    }
}
