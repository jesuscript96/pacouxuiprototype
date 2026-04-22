<?php

namespace App\Contracts;

use App\DTO\ResultadoValidacion;
use App\Models\CandidatoReclutamiento;

interface ValidadorDocumentoInterface
{
    /**
     * Validar un documento de candidato.
     */
    public function validar(
        CandidatoReclutamiento $candidato,
        string $nombreCampo,
        string $rutaArchivo,
    ): ResultadoValidacion;

    /**
     * Nombre del tipo de documento que valida.
     */
    public function tipoDocumento(): string;
}
