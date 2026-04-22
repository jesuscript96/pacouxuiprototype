<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AutorizadorEtapaAprobacion;
use App\Models\CategoriaSolicitud;
use App\Models\EtapaFlujoAprobacion;
use App\Models\PreguntaSolicitud;
use App\Models\TipoSolicitud;
use App\Models\ValorPreguntaSolicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Persistencia de tipo de permiso, etapas, autorizadores y preguntas.
 *
 * Toda la escritura relevante va dentro de {@see DB::transaction}
 * en `crear` y `actualizar` (todo o nada).
 *
 * BL: Las imágenes de preguntas se suben con Filament a
 * `companies/{empresa}/tipos-solicitud/tmp/` y al guardar se mueven a
 * `companies/{empresa}/tipos-solicitud/{tipo_solicitud_id}/` (evita depender
 * de {@see ArchivoService::guardar()} con TemporaryUploadedFile).
 */
class TipoSolicitudPersistService
{
    public function __construct(
        private ArchivoService $archivoService,
    ) {}

    /**
     * Prefijo requerido por Filament (statePath `data`) para que los
     * errores de validación aparezcan en el campo correcto del formulario.
     */
    private static function claveValidacionFormulario(string $campo): string
    {
        return 'data.'.$campo;
    }

    /**
     * Crea un nuevo tipo de solicitud con sus etapas de aprobación y preguntas.
     *
     * @param  array<string, mixed>  $data  Datos del formulario (incluye etapas y preguntas)
     * @param  int  $empresaId  ID de la empresa tenant
     */
    public function crear(array $data, int $empresaId): TipoSolicitud
    {
        return DB::transaction(function () use ($data, $empresaId): TipoSolicitud {
            $this->validarCategoria((int) ($data['categoria_solicitud_id'] ?? 0), $empresaId);
            $this->validarEtapas($data['etapas'] ?? [], $empresaId);

            $tipo = TipoSolicitud::create($this->atributosPrincipales($data));

            $this->sincronizarEtapas($tipo, $data['etapas'] ?? []);
            $this->sincronizarPreguntas($tipo, $data['preguntas'] ?? [], $empresaId);

            return $tipo->fresh();
        });
    }

    /**
     * Actualiza un tipo de solicitud existente, recrea etapas y preguntas.
     *
     * BL: las etapas y preguntas se eliminan y recrean (no se actualizan in-place)
     * para simplificar el manejo de reordenamientos y eliminaciones.
     *
     * @param  array<string, mixed>  $data  Datos del formulario
     * @param  int  $empresaId  ID de la empresa tenant
     */
    public function actualizar(TipoSolicitud $tipo, array $data, int $empresaId): TipoSolicitud
    {
        return DB::transaction(function () use ($tipo, $data, $empresaId): TipoSolicitud {
            $this->validarCategoria((int) ($data['categoria_solicitud_id'] ?? 0), $empresaId);
            $this->validarEtapas($data['etapas'] ?? [], $empresaId);

            $tipo->update($this->atributosPrincipales($data));

            $tipo->etapasFlujoAprobacion()->delete();
            $this->sincronizarEtapas($tipo, $data['etapas'] ?? []);

            $rutasImagenAnteriores = $this->coleccionRutasCanonicoImagenesPreguntas($tipo);

            $this->eliminarPreguntasExistentes($tipo);
            $this->sincronizarPreguntas($tipo, $data['preguntas'] ?? [], $empresaId);

            $tipoActualizado = $tipo->fresh(['preguntasSolicitud']);
            $rutasImagenNuevas = $this->coleccionRutasCanonicoImagenesPreguntas($tipoActualizado);
            $this->eliminarImagenesPreguntaHuérfanas($rutasImagenAnteriores, $rutasImagenNuevas);

            return $tipoActualizado;
        });
    }

    /**
     * Valida que la categoría exista y pertenezca a la empresa (o sea global).
     */
    private function validarCategoria(int $categoriaId, int $empresaId): void
    {
        if ($categoriaId <= 0) {
            throw ValidationException::withMessages([
                self::claveValidacionFormulario('categoria_solicitud_id') => 'La categoría es obligatoria.',
            ]);
        }

        $categoria = CategoriaSolicitud::query()->find($categoriaId);
        if ($categoria === null) {
            throw ValidationException::withMessages([
                self::claveValidacionFormulario('categoria_solicitud_id') => 'La categoría no existe.',
            ]);
        }

        if ($categoria->empresa_id !== null && (int) $categoria->empresa_id !== $empresaId) {
            abort(403);
        }
    }

    /**
     * Valida que las etapas tengan al menos una definida y que los autorizadores
     * o niveles de jerarquía sean válidos para la empresa.
     *
     * @param  array<int, mixed>  $etapas
     */
    private function validarEtapas(array $etapas, int $empresaId): void
    {
        if ($etapas === []) {
            throw ValidationException::withMessages([
                self::claveValidacionFormulario('etapas') => 'Debe definir al menos una etapa de autorización.',
            ]);
        }

        $idsAutorizadoresPermitidos = TipoSolicitudAutorizacionOpciones::idsAutorizadoresPorNombrePermitidos($empresaId);
        $nivelesPermitidos = TipoSolicitudAutorizacionOpciones::nivelesJerarquiaPermitidosComoString($empresaId);

        foreach ($etapas as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            $nivelAuth = $fila['nivel_autorizacion'] ?? 'POR NOMBRE';
            if ($nivelAuth === 'POR NOMBRE' && ($fila['usuarios'] ?? []) === []) {
                throw ValidationException::withMessages([
                    self::claveValidacionFormulario('etapas') => 'En modo «Por nombre» cada etapa debe tener al menos un autorizador.',
                ]);
            }
            if ($nivelAuth === 'JERARQUIA' && ($fila['niveles_jerarquia'] ?? []) === []) {
                throw ValidationException::withMessages([
                    self::claveValidacionFormulario('etapas') => 'En modo «Jerarquía» cada etapa debe tener al menos un nivel seleccionado.',
                ]);
            }

            if ($nivelAuth === 'POR NOMBRE') {
                foreach ($fila['usuarios'] ?? [] as $uid) {
                    if (! in_array((int) $uid, $idsAutorizadoresPermitidos, true)) {
                        throw ValidationException::withMessages([
                            self::claveValidacionFormulario('etapas') => 'Uno o más autorizadores no son válidos: deben pertenecer a la empresa, tener área y puesto en la ficha y existir en la tabla `jefes`.',
                        ]);
                    }
                }
            }

            if ($nivelAuth === 'JERARQUIA') {
                foreach ($fila['niveles_jerarquia'] ?? [] as $niv) {
                    $nivStr = (string) $niv;
                    if (! in_array($nivStr, $nivelesPermitidos, true)) {
                        throw ValidationException::withMessages([
                            self::claveValidacionFormulario('etapas') => 'Uno o más niveles de jerarquía no están configurados en `jefes` para esta empresa.',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Extrae los atributos principales del tipo de solicitud del array de datos.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function atributosPrincipales(array $data): array
    {
        $tieneVigencia = (bool) ($data['tiene_vigencia'] ?? false);

        return [
            'nombre' => $data['nombre'],
            'estado' => $data['estado'] ?? 1,
            'rango_fechas' => $data['rango_fechas'],
            'unidad_tiempo' => $data['unidad_tiempo'],
            'vigencia_solicitud' => $tieneVigencia,
            'fecha_vigencia' => $tieneVigencia ? ($data['fecha_vigencia'] ?? null) : null,
            'descripcion' => $data['descripcion'],
            'categoria_solicitud_id' => $data['categoria_solicitud_id'],
        ];
    }

    /**
     * Crea las etapas de flujo de aprobación y sus autorizadores.
     *
     * Cada etapa puede ser "POR NOMBRE" (usuarios específicos) o
     * "JERARQUIA" (niveles jerárquicos de la empresa).
     *
     * @param  array<int, mixed>  $etapas
     */
    private function sincronizarEtapas(TipoSolicitud $tipo, array $etapas): void
    {
        $numeroEtapa = 0;
        foreach ($etapas as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $numeroEtapa++;
            $nivelAuth = $fila['nivel_autorizacion'] ?? 'POR NOMBRE';

            $etapa = EtapaFlujoAprobacion::create([
                'tipo_solicitud_id' => $tipo->id,
                'etapa' => $numeroEtapa,
                'nivel_autorizacion' => $nivelAuth,
            ]);

            if ($nivelAuth === 'POR NOMBRE') {
                foreach ($fila['usuarios'] ?? [] as $uid) {
                    AutorizadorEtapaAprobacion::create([
                        'etapa_flujo_aprobacion_id' => $etapa->id,
                        'usuario_id' => (int) $uid,
                        'nivel' => null,
                    ]);
                }
            } else {
                foreach ($fila['niveles_jerarquia'] ?? [] as $niv) {
                    AutorizadorEtapaAprobacion::create([
                        'etapa_flujo_aprobacion_id' => $etapa->id,
                        'nivel' => (string) $niv,
                        'usuario_id' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Rutas de almacenamiento de imágenes de preguntas, normalizadas (S3 completo o legacy).
     *
     * BL: Misma resolución que {@see PreguntaSolicitud::imagenUrl()} sin generar URL.
     *
     * @return list<string>
     */
    private function coleccionRutasCanonicoImagenesPreguntas(TipoSolicitud $tipo): array
    {
        return $tipo->preguntasSolicitud()
            ->pluck('imagen')
            ->map(fn (?string $img): ?string => $this->canonicoRutaImagenPregunta($img, (int) $tipo->id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function canonicoRutaImagenPregunta(?string $imagenRaw, int $tipoSolicitudId): ?string
    {
        if (blank($imagenRaw)) {
            return null;
        }

        $imagenRaw = trim(str_replace('\\', '/', $imagenRaw));

        if ($imagenRaw === '') {
            return null;
        }

        $ruta = str_contains($imagenRaw, '/')
            ? ltrim($imagenRaw, '/')
            : TipoSolicitud::rutaRelativaImagenes($tipoSolicitudId).'/'.$imagenRaw;

        return $ruta;
    }

    /**
     * Quita del disco las imágenes que dejaron de estar referenciadas tras guardar.
     *
     * @param  list<string>  $rutasAnteriores
     * @param  list<string>  $rutasNuevas
     */
    private function eliminarImagenesPreguntaHuérfanas(array $rutasAnteriores, array $rutasNuevas): void
    {
        $setNuevo = array_flip($rutasNuevas);
        foreach ($rutasAnteriores as $ruta) {
            if ($ruta === '' || isset($setNuevo[$ruta])) {
                continue;
            }
            $this->archivoService->eliminar($ruta);
        }
    }

    /**
     * Elimina todas las preguntas existentes y sus valores/opciones (force delete).
     */
    private function eliminarPreguntasExistentes(TipoSolicitud $tipo): void
    {
        foreach ($tipo->preguntasSolicitud()->with('valores')->get() as $pregunta) {
            foreach ($pregunta->valores as $valor) {
                $valor->forceDelete();
            }
            $pregunta->forceDelete();
        }
    }

    /**
     * Crea las preguntas de la solicitud con sus valores/opciones e imágenes.
     *
     * @param  array<int, mixed>  $preguntas
     * @param  int  $empresaId  ID de la empresa (para construir la ruta S3)
     */
    private function sincronizarPreguntas(TipoSolicitud $tipo, array $preguntas, int $empresaId): void
    {
        foreach ($preguntas as $idx => $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $tipoPregunta = $fila['tipo'] ?? 'open_question';
            $numero = isset($fila['numero']) ? (int) $fila['numero'] : ($idx + 1);

            $minResp = match ($tipoPregunta) {
                'multiple_choice' => (int) ($fila['min_respuestas'] ?? 1),
                default => 0,
            };
            $maxResp = match ($tipoPregunta) {
                'multiple_choice' => (int) ($fila['max_respuestas'] ?? 1),
                default => 0,
            };

            $rutaImagen = $this->resolverImagen($fila, $tipo->id, $empresaId, $numero);

            $pregunta = PreguntaSolicitud::create([
                'tipo_solicitud_id' => $tipo->id,
                'tipo' => $tipoPregunta,
                'titulo' => $fila['titulo'] ?? '',
                'subtitulo' => $fila['subtitulo'] ?? null,
                'imagen' => $rutaImagen,
                'min_respuestas' => $minResp,
                'max_respuestas' => $maxResp,
                'numero' => $numero,
            ]);

            $this->crearValoresPregunta($pregunta, $tipoPregunta, $fila);
        }
    }

    /**
     * Resuelve la ruta final de la imagen de una pregunta en disco.
     *
     * BL: Filament sube primero a `companies/{empresa}/tipos-solicitud/tmp/`; aquí
     * se mueve a la carpeta definitiva con el ID del tipo. Si la ruta ya es
     * definitiva o legacy, se deja igual.
     *
     * @param  array<string, mixed>  $fila  Datos de la pregunta del repeater
     * @param  int  $tipoSolicitudId  ID del tipo de solicitud (para el path final; no es el id de pregunta)
     * @param  int  $empresaId  ID de la empresa tenant
     * @param  int  $numero  Número de pregunta en el formulario (para nombre de archivo legible)
     * @return string Ruta completa del archivo en disco, o vacío si no hay imagen
     */
    private function resolverImagen(array $fila, int $tipoSolicitudId, int $empresaId, int $numero): string
    {
        $subido = $fila['imagen'] ?? null;
        $imagenActual = (string) ($fila['imagen_actual'] ?? '');
        $estado = $this->extraerEstadoImagen($subido);

        if ($estado instanceof TemporaryUploadedFile) {
            $ruta = $this->persistirImagenDesdeTemporaryUploadedFile($estado, $tipoSolicitudId, $empresaId, $numero);

            return $ruta !== '' ? $ruta : $imagenActual;
        }

        if ($estado !== '') {
            $final = $this->moverImagenDesdeTmpSiAplica($estado, $tipoSolicitudId, $empresaId);
            $tmpInfo = $this->parseRutaTmpTiposSolicitud($final);

            if ($tmpInfo !== null && $tmpInfo['empresa'] === $empresaId && ! $this->archivoService->existe($final)) {
                return $imagenActual;
            }

            return $final;
        }

        // BL: Si el usuario vació el FileUpload, `imagen` llega vacío; NO restaurar
        // `imagen_actual` (el hidden seguiría con la ruta vieja y repondría la imagen).
        return '';
    }

    /**
     * Normaliza el valor del campo `imagen` del repeater (string, array con una ruta o TemporaryUploadedFile).
     *
     * @return TemporaryUploadedFile|string Cadena vacía si no hay archivo nuevo
     */
    private function extraerEstadoImagen(mixed $subido): TemporaryUploadedFile|string
    {
        if ($subido instanceof TemporaryUploadedFile) {
            return $subido;
        }

        if (is_array($subido)) {
            $first = reset($subido);
            $subido = is_string($first) ? $first : '';
        }

        if (! is_string($subido) || $subido === '') {
            return '';
        }

        return $this->normalizarRutaRelativa($subido);
    }

    /**
     * Sube al disco de archivos cuando Livewire aún entrega TemporaryUploadedFile (p. ej. algunos flujos al editar).
     */
    private function persistirImagenDesdeTemporaryUploadedFile(
        TemporaryUploadedFile $subido,
        int $tipoSolicitudId,
        int $empresaId,
        int $numero,
    ): string {
        $ext = $subido->getClientOriginalExtension();
        if ($ext === '') {
            $ext = 'jpg';
        }

        $nombre = 'imagen_'.$numero.'_'.time().'.'.$ext;
        $destino = "companies/{$empresaId}/tipos-solicitud/{$tipoSolicitudId}/{$nombre}";
        $disco = Storage::disk($this->archivoService->nombreDisco());
        $stream = $subido->readStream();

        if (! is_resource($stream)) {
            return '';
        }

        try {
            $disco->put($destino, $stream, ['visibility' => 'public']);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $subido->delete();

        return $destino;
    }

    private function normalizarRutaRelativa(string $ruta): string
    {
        $ruta = trim(str_replace('\\', '/', $ruta));

        if ($ruta === '') {
            return '';
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            $path = parse_url($ruta, PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $pos = strpos($path, 'companies/');
                $ruta = $pos !== false ? substr($path, $pos) : ltrim($path, '/');
            }
        }

        return ltrim($ruta, '/');
    }

    /**
     * Detecta rutas bajo `companies/{n}/tipos-solicitud/tmp/{archivo}`.
     *
     * @return array{empresa: int, archivo: string}|null
     */
    private function parseRutaTmpTiposSolicitud(string $ruta): ?array
    {
        if (! preg_match('#^companies/(\d+)/tipos-solicitud/tmp/([^/]+)$#', $ruta, $m)) {
            return null;
        }

        return ['empresa' => (int) $m[1], 'archivo' => $m[2]];
    }

    /**
     * Copia el archivo leyendo bytes/stream (útil cuando move/copy del adapter en S3 fallan).
     */
    private function copiarArchivoPorLecturaTotal(string $origen, string $destino): void
    {
        $disco = Storage::disk($this->archivoService->nombreDisco());

        try {
            $stream = $disco->readStream($origen);
            if (is_resource($stream)) {
                try {
                    $disco->put($destino, $stream, ['visibility' => 'public']);
                } finally {
                    fclose($stream);
                }

                return;
            }
        } catch (\Throwable) {
            // Intentar get() abajo.
        }

        try {
            $bytes = $disco->get($origen);
            if ($bytes !== null && $bytes !== '') {
                $disco->put($destino, $bytes, ['visibility' => 'public']);
            }
        } catch (\Throwable) {
            // Sin archivo legible en origen.
        }
    }

    /**
     * Si la imagen está en la carpeta temporal de subida, la mueve a la ruta canónica del tipo.
     *
     * @return string Ruta final a persistir en `preguntas_solicitud.imagen`
     */
    private function moverImagenDesdeTmpSiAplica(string $ruta, int $tipoSolicitudId, int $empresaId): string
    {
        $ruta = $this->normalizarRutaRelativa($ruta);
        if ($ruta === '') {
            return '';
        }

        $parsed = $this->parseRutaTmpTiposSolicitud($ruta);
        if ($parsed === null) {
            return $ruta;
        }

        if ($parsed['empresa'] !== $empresaId) {
            return $ruta;
        }

        $nombreArchivo = $parsed['archivo'];
        $destino = "companies/{$empresaId}/tipos-solicitud/{$tipoSolicitudId}/{$nombreArchivo}";

        if ($ruta === $destino) {
            return $destino;
        }

        if ($this->archivoService->existe($destino)) {
            if ($this->archivoService->existe($ruta)) {
                $this->archivoService->eliminar($ruta);
            }

            return $destino;
        }

        if ($this->archivoService->existe($ruta)) {
            $movido = $this->archivoService->mover($ruta, $destino);
            if (! $movido && $this->archivoService->copiar($ruta, $destino)) {
                $this->archivoService->eliminar($ruta);
            }
        }

        if (! $this->archivoService->existe($destino) && $this->archivoService->existe($ruta)) {
            $this->copiarArchivoPorLecturaTotal($ruta, $destino);
        }

        if ($this->archivoService->existe($destino)) {
            if ($this->archivoService->existe($ruta)) {
                $this->archivoService->eliminar($ruta);
            }

            return $destino;
        }

        return $ruta;
    }

    /**
     * Crea los valores/opciones de una pregunta.
     *
     * Para preguntas abiertas: crea un único valor vacío.
     * Para opción/selección múltiple: crea un valor por cada opción
     * y opcionalmente uno marcado como respuesta personalizada.
     *
     * @param  array<string, mixed>  $fila  Datos de la pregunta
     */
    private function crearValoresPregunta(PreguntaSolicitud $pregunta, string $tipoPregunta, array $fila): void
    {
        if ($tipoPregunta === 'open_question') {
            ValorPreguntaSolicitud::create([
                'pregunta_solicitud_id' => $pregunta->id,
                'indice' => 0,
                'titulo' => '',
                'respuesta_personalizada' => false,
            ]);

            return;
        }

        $opciones = $fila['opciones'] ?? [];
        $k = 0;
        foreach ($opciones as $op) {
            $titulo = is_array($op) ? (string) ($op['titulo'] ?? '') : (string) $op;
            ValorPreguntaSolicitud::create([
                'pregunta_solicitud_id' => $pregunta->id,
                'indice' => $k,
                'titulo' => $titulo,
                'respuesta_personalizada' => false,
            ]);
            $k++;
        }

        $textoPersonalizado = $fila['texto_personalizado'] ?? null;
        if (filled($textoPersonalizado)) {
            ValorPreguntaSolicitud::create([
                'pregunta_solicitud_id' => $pregunta->id,
                'indice' => $k,
                'titulo' => (string) $textoPersonalizado,
                'respuesta_personalizada' => true,
            ]);
        }
    }
}
