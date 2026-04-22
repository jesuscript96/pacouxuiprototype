<?php

namespace App\Services;

use App\Models\CandidatoReclutamiento;
use App\Models\Empresa;
use App\Models\HistorialEstatusCandidato;
use App\Models\Vacante;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PostulacionService
{
    public function __construct(
        private ArchivoService $archivoService,
    ) {}

    public function crearCandidato(Vacante $vacante, array $datos): CandidatoReclutamiento
    {
        return DB::transaction(function () use ($vacante, $datos): CandidatoReclutamiento {
            $campos = $datos['campos'] ?? [];
            $archivosSubidos = $datos['archivos'] ?? [];

            $candidato = CandidatoReclutamiento::create([
                'vacante_id' => $vacante->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
                'valores_formulario' => $campos,
                'archivos' => [],
                'curp' => $this->extraerCampo($campos, ['curp']) ? strtoupper($this->extraerCampo($campos, ['curp'])) : null,
                'nombre_completo' => $this->construirNombreCompleto($campos),
                'email' => $this->extraerCampo($campos, ['email', 'correo', 'correo_electronico']),
                'telefono' => $this->extraerCampo($campos, ['telefono', 'celular', 'phone', 'tel']),
            ]);

            $archivosGuardados = $this->procesarArchivos(
                $archivosSubidos,
                $vacante->empresa,
                $candidato->id,
            );

            if ($archivosGuardados !== []) {
                $candidato->update(['archivos' => $archivosGuardados]);
            }

            HistorialEstatusCandidato::create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
                'fecha_inicio' => now(),
            ]);

            return $candidato;
        });
    }

    /**
     * @param  array<string, UploadedFile>  $archivos
     * @return array<string, array{path: string, nombre_original: string, mime_type: string|null, size: int|false, is_valid: null, data_is_valid: null, uploaded_at: string}>
     */
    private function procesarArchivos(array $archivos, Empresa $empresa, int $candidatoId): array
    {
        $resultado = [];

        foreach ($archivos as $nombreCampo => $archivo) {
            if ($archivo instanceof UploadedFile && $archivo->isValid()) {
                $ruta = $this->archivoService->guardar(
                    archivo: $archivo,
                    empresa: $empresa,
                    modulo: 'candidatos',
                    registroId: $candidatoId,
                    nombre: $nombreCampo,
                );

                $resultado[$nombreCampo] = [
                    'path' => $ruta,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'mime_type' => $archivo->getMimeType(),
                    'size' => $archivo->getSize(),
                    'is_valid' => null,
                    'data_is_valid' => null,
                    'uploaded_at' => now()->toIso8601String(),
                ];
            }
        }

        return $resultado;
    }

    private function extraerCampo(array $campos, array $posiblesNombres): ?string
    {
        foreach ($posiblesNombres as $nombre) {
            if (! empty($campos[$nombre])) {
                return $campos[$nombre];
            }
        }

        return null;
    }

    private function construirNombreCompleto(array $campos): ?string
    {
        $partes = [];

        $nombre = $this->extraerCampo($campos, ['nombre', 'nombres', 'primer_nombre']);
        $apellidoPaterno = $this->extraerCampo($campos, ['apellido_paterno', 'primer_apellido', 'paterno']);
        $apellidoMaterno = $this->extraerCampo($campos, ['apellido_materno', 'segundo_apellido', 'materno']);

        if ($nombre) {
            $partes[] = $nombre;
        }
        if ($apellidoPaterno) {
            $partes[] = $apellidoPaterno;
        }
        if ($apellidoMaterno) {
            $partes[] = $apellidoMaterno;
        }

        if ($partes !== []) {
            return implode(' ', $partes);
        }

        return $this->extraerCampo($campos, ['nombre_completo', 'full_name']);
    }
}
