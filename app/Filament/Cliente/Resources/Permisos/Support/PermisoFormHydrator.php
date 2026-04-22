<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Support;

use App\Models\TipoSolicitud;

/**
 * Hidrata el formulario de edición con datos de relaciones (etapas y preguntas).
 *
 * Transforma los modelos Eloquent al formato que esperan los Repeaters
 * de Filament, incluyendo las rutas de imágenes de preguntas.
 */
final class PermisoFormHydrator
{
    /**
     * Mezcla datos de etapas de aprobación y preguntas en el array del formulario.
     *
     * Para las imágenes de preguntas, maneja dos formatos de ruta:
     * - Nuevo (S3): ruta completa almacenada en BD (companies/{id}/...)
     * - Legacy: solo filename, se construye con rutaRelativaImagenes()
     *
     * @param  array<string, mixed>  $data  Datos base del modelo
     * @return array<string, mixed> Datos enriquecidos con etapas y preguntas
     */
    public static function mergeRelaciones(TipoSolicitud $record, array $data): array
    {
        $record->loadMissing([
            'etapasFlujoAprobacion.autorizadoresEtapaAprobacion',
            'preguntasSolicitud.valores',
        ]);

        $data['tiene_vigencia'] = (bool) $record->vigencia_solicitud;

        $data['etapas'] = $record->etapasFlujoAprobacion->sortBy('etapa')->values()->map(function ($e): array {
            if ($e->nivel_autorizacion === 'POR NOMBRE') {
                return [
                    'nivel_autorizacion' => 'POR NOMBRE',
                    'usuarios' => $e->autorizadoresEtapaAprobacion->pluck('usuario_id')->filter()->values()->all(),
                ];
            }

            return [
                'nivel_autorizacion' => 'JERARQUIA',
                'niveles_jerarquia' => $e->autorizadoresEtapaAprobacion->pluck('nivel')->filter()->values()->all(),
            ];
        })->all();

        $data['preguntas'] = $record->preguntasSolicitud->sortBy('numero')->values()->map(function ($q) use ($record): array {
            $row = [
                'tipo' => $q->tipo,
                'titulo' => $q->titulo,
                'subtitulo' => $q->subtitulo,
                'numero' => $q->numero,
                'min_respuestas' => $q->min_respuestas,
                'max_respuestas' => $q->max_respuestas,
                'imagen_actual' => $q->imagen,
            ];

            if ($q->imagen) {
                $row['imagen'] = str_contains($q->imagen, '/')
                    ? $q->imagen
                    : TipoSolicitud::rutaRelativaImagenes((int) $record->id).'/'.$q->imagen;
            }

            if (in_array($q->tipo, ['multiple_option', 'multiple_choice'], true)) {
                $vals = $q->valores->sortBy('indice');
                $personalizado = $vals->first(fn ($v) => $v->respuesta_personalizada);
                $normales = $vals->where('respuesta_personalizada', false);
                $row['opciones'] = $normales->map(fn ($v): array => ['titulo' => (string) $v->titulo])->values()->all();
                $row['texto_personalizado'] = $personalizado?->titulo;
            }

            return $row;
        })->all();

        return $data;
    }
}
