<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CandidatoReclutamiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CandidatoPdfService
{
    public function generarReporte(CandidatoReclutamiento $candidato): Response
    {
        $candidato->load([
            'vacante.empresa',
            'vacante.camposFormulario' => fn ($q) => $q->orderBy('orden'),
            'historialEstatus.creadoPor',
            'mensajes.usuario',
        ]);

        $camposFormulario = $candidato->vacante->camposFormulario
            ->where('tipo', '!=', 'file');

        $valoresFormulario = $candidato->valores_formulario ?? [];

        $pdf = Pdf::loadView('exports.pdf.candidato-reporte', [
            'candidato' => $candidato,
            'vacante' => $candidato->vacante,
            'empresa' => $candidato->vacante->empresa,
            'camposFormulario' => $camposFormulario,
            'valoresFormulario' => $valoresFormulario,
        ]);

        $nombreArchivo = sprintf(
            'candidato-%s-%s.pdf',
            $candidato->id,
            now()->format('Y-m-d'),
        );

        return $pdf->download($nombreArchivo);
    }
}
