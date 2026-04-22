<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Enums\EstadoNotificacionPush;
use App\Models\Carpeta;
use App\Models\DocumentoCorporativo;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\Subcarpeta;
use App\Models\User;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use App\Support\NombreArchivoDocumentosCorporativos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * BL: Lógica de carpetas / subcarpetas / archivos de documentos corporativos (panel Cliente).
 * Notificaciones push reutilizan {@see NotificacionPush}, OneSignal y Pumble vía {@see EnviarNotificacionPushJob}.
 */
class DocumentosCorporativosCarpetaService
{
    public function __construct(
        protected ResolverDestinatariosService $resolverDestinatarios,
        protected EnviarNotificacionPushAction $enviarNotificacionPushAction,
        protected ArchivoService $archivoService,
    ) {}

    /**
     * @param  array{
     *     ubicacion_ids?: array<int|string>,
     *     departamento_ids?: array<int|string>,
     *     area_ids?: array<int|string>,
     *     puesto_ids?: array<int|string>,
     *     nombre: string,
     *     subcarpetas?: list<array{nombre?: string|null, archivos?: mixed}>,
     *     staging_id?: string|null,
     * }  $datos
     */
    public function crearDesdeWizard(Empresa $empresa, ?User $usuario, array $datos): Carpeta
    {
        $this->validarSeleccionCatalogos($datos);

        $nombre = trim((string) ($datos['nombre'] ?? ''));
        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre de la carpeta es obligatorio.']]);
        }

        if ($this->existeCarpetaMismoNombre($empresa->id, $nombre)) {
            throw ValidationException::withMessages(['nombre' => ['Ya existe una carpeta con este nombre para la empresa.']]);
        }

        $ubicacionIds = $this->normalizarIds($datos['ubicacion_ids'] ?? []);
        $departamentoIds = $this->normalizarIds($datos['departamento_ids'] ?? []);
        $areaIds = $this->normalizarIds($datos['area_ids'] ?? []);
        $puestoIds = $this->normalizarIds($datos['puesto_ids'] ?? []);

        $rutaBase = $this->resolverRutaCarpetaFisica($empresa, $nombre);

        return DB::transaction(function () use ($empresa, $usuario, $nombre, $rutaBase, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds, $datos): Carpeta {
            $disk = $this->archivoService->disco();
            if ($disk->exists($rutaBase)) {
                throw ValidationException::withMessages(['nombre' => ['La ruta física ya existe; elija otro nombre.']]);
            }
            $disk->makeDirectory($rutaBase);

            /** @var Carpeta $carpeta */
            $carpeta = Carpeta::query()->create([
                'nombre' => $nombre,
                'empresa_id' => $empresa->id,
                'url' => $rutaBase,
                'tipo' => Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS,
                'usuario_id' => $usuario?->id,
            ]);

            $this->sincronizarPivotes($carpeta, $empresa->id, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds);

            $nombresSub = $this->extraerNombresSubcarpetas($datos['subcarpetas'] ?? []);
            foreach ($nombresSub as $nombreSub) {
                $this->crearSubcarpetaInterno($carpeta, $nombreSub);
            }

            $stagingId = isset($datos['staging_id']) ? (string) $datos['staging_id'] : '';
            if ($stagingId !== '') {
                $this->moverArchivosStagingARaiz($stagingId, $rutaBase, 'raiz');
                $this->moverArchivosStagingSubcarpetas($stagingId, $carpeta->fresh('subcarpetas'), $datos['subcarpetas'] ?? []);
            }

            $carpeta = $carpeta->fresh(['subcarpetas']);

            $huboArchivos = $this->carpetaTieneAlgunArchivoEnDisco($carpeta);

            if (! $huboArchivos) {
                $this->notificarCarpetaCreada($empresa, $carpeta, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds, $usuario);

                foreach ($carpeta->subcarpetas as $sub) {
                    $this->notificarSubcarpetaCreada($empresa, $carpeta, $sub, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds, $usuario);
                }
            }

            $this->registrarDocumentosInicialesEnDisco($carpeta, $empresa, $usuario);

            return $carpeta->fresh(['subcarpetas']);
        });
    }

    /**
     * @param  array{
     *     ubicacion_ids?: array<int|string>,
     *     departamento_ids?: array<int|string>,
     *     area_ids?: array<int|string>,
     *     puesto_ids?: array<int|string>,
     *     nombre: string,
     *     subcarpetas?: list<array{nombre?: string|null, archivos?: mixed}>,
     *     subcarpetas_nuevas?: list<array{nombre?: string|null}>,
     * }  $datos
     */
    public function actualizarCarpeta(Carpeta $carpeta, Empresa $empresa, ?User $usuario, array $datos): Carpeta
    {
        $this->validarSeleccionCatalogos($datos);

        $nombre = trim((string) ($datos['nombre'] ?? ''));
        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre de la carpeta es obligatorio.']]);
        }

        if ($this->existeCarpetaMismoNombre($empresa->id, $nombre, $carpeta->id)) {
            throw ValidationException::withMessages(['nombre' => ['Ya existe una carpeta con este nombre para la empresa.']]);
        }

        $ubicacionIds = $this->normalizarIds($datos['ubicacion_ids'] ?? []);
        $departamentoIds = $this->normalizarIds($datos['departamento_ids'] ?? []);
        $areaIds = $this->normalizarIds($datos['area_ids'] ?? []);
        $puestoIds = $this->normalizarIds($datos['puesto_ids'] ?? []);

        $disk = $this->archivoService->disco();

        return DB::transaction(function () use ($carpeta, $empresa, $nombre, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds, $datos, $disk): Carpeta {
            $rutaAnterior = $carpeta->url;
            $rutaNueva = $carpeta->nombre === $nombre
                ? $carpeta->url
                : $this->resolverRutaCarpetaFisica($empresa, $nombre);

            if ($rutaAnterior !== $rutaNueva && $disk->exists($rutaNueva)) {
                throw ValidationException::withMessages(['nombre' => ['La ruta física ya existe; elija otro nombre.']]);
            }

            if ($rutaAnterior !== $rutaNueva) {
                if ($disk->exists($rutaAnterior)) {
                    $disk->move($rutaAnterior, $rutaNueva);
                } else {
                    $disk->makeDirectory($rutaNueva);
                }
            }

            $carpeta->nombre = $nombre;
            $carpeta->url = $rutaNueva;
            $carpeta->empresa_id = $empresa->id;
            $carpeta->save();

            $carpeta->load('subcarpetas');
            foreach ($carpeta->subcarpetas as $sub) {
                $segmento = basename((string) $sub->url);
                $sub->url = $rutaNueva.'/'.$segmento;
                $sub->save();
            }

            $this->sincronizarPivotes($carpeta, $empresa->id, $ubicacionIds, $departamentoIds, $areaIds, $puestoIds);

            $nuevas = $this->extraerNombresSubcarpetas($datos['subcarpetas_nuevas'] ?? []);
            foreach ($nuevas as $nombreSub) {
                $this->crearSubcarpetaInterno($carpeta->fresh(), $nombreSub);
            }

            return $carpeta->fresh(['subcarpetas']);
        });
    }

    public function actualizarNombreSubcarpeta(Subcarpeta $subcarpeta, string $nuevoNombre, ?User $usuario): Subcarpeta
    {
        $nuevoNombre = trim($nuevoNombre);
        if ($nuevoNombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre de la subcarpeta es obligatorio.']]);
        }

        $carpeta = $subcarpeta->carpeta;
        if ($carpeta === null) {
            throw ValidationException::withMessages(['subcarpeta' => ['Carpeta no encontrada.']]);
        }

        $disk = $this->archivoService->disco();
        $rutaNueva = $carpeta->url.'/'.$this->segmentoDirectorioSeguro($nuevoNombre);

        if ($subcarpeta->nombre !== $nuevoNombre && Subcarpeta::query()
            ->where('carpeta_id', $carpeta->id)
            ->where('nombre', $nuevoNombre)
            ->where('id', '!=', $subcarpeta->id)
            ->exists()) {
            throw ValidationException::withMessages(['nombre' => ['Ya existe una subcarpeta con ese nombre.']]);
        }

        if ($disk->exists($rutaNueva) && $rutaNueva !== $subcarpeta->url) {
            throw ValidationException::withMessages(['nombre' => ['La ruta física ya existe.']]);
        }

        if ($disk->exists($subcarpeta->url) && $subcarpeta->url !== $rutaNueva) {
            $disk->move($subcarpeta->url, $rutaNueva);
        } elseif (! $disk->exists($rutaNueva)) {
            $disk->makeDirectory($rutaNueva);
        }

        $subcarpeta->nombre = $nuevoNombre;
        $subcarpeta->url = $rutaNueva;
        $subcarpeta->save();

        return $subcarpeta;
    }

    public function eliminarSubcarpeta(Subcarpeta $subcarpeta): void
    {
        $subcarpeta->delete();
    }

    /**
     * @param  list<string>  $rutasRelativasUploads
     */
    public function registrarArchivosSubidosYNotificar(
        Carpeta $carpeta,
        Empresa $empresa,
        ?Subcarpeta $subcarpeta,
        array $rutasRelativasUploads,
        ?User $usuario,
    ): void {
        if ($rutasRelativasUploads === []) {
            return;
        }

        $carpeta->loadMissing(['ubicaciones', 'departamentos', 'areas', 'puestos']);
        $ubicacionIds = $carpeta->ubicaciones->pluck('id')->all();
        $departamentoIds = $carpeta->departamentos->pluck('id')->all();
        $areaIds = $carpeta->areas->pluck('id')->all();
        $puestoIds = $carpeta->puestos->pluck('id')->all();

        $idsUsuarios = $this->userIdsFiltrados(
            $empresa,
            $ubicacionIds,
            $departamentoIds,
            $areaIds,
            $puestoIds
        );

        $ahora = now();
        $nombreSub = $subcarpeta?->nombre;

        DB::transaction(function () use ($carpeta, $idsUsuarios, $rutasRelativasUploads, $ahora, $nombreSub): void {
            foreach ($rutasRelativasUploads as $ruta) {
                $nombreArchivo = basename((string) $ruta);
                foreach ($idsUsuarios as $uid) {
                    DocumentoCorporativo::query()->create([
                        'user_id' => $uid,
                        'carpeta_id' => $carpeta->id,
                        'subcarpeta' => $nombreSub,
                        'nombre_documento' => $nombreArchivo,
                        'fecha_carga' => $ahora,
                    ]);
                }
            }
        });

        $mensaje = $empresa->nombre.' ha subido un nuevo documento. Abra la app para consultarlo.';
        $this->dispararNotificacionPushDocumento(
            $empresa,
            $mensaje,
            $ubicacionIds,
            $departamentoIds,
            $areaIds,
            $puestoIds,
            [
                'tipo' => 'documentos_corporativos',
                'carpeta_id' => $carpeta->id,
                'subcarpeta' => $nombreSub,
                'archivos' => array_map('basename', $rutasRelativasUploads),
            ],
            $usuario
        );
    }

    /**
     * Elimina un archivo del disco (Wasabi/local) y las filas de documentos_corporativos asociadas a ese nombre.
     */
    public function eliminarArchivoDeCarpeta(Carpeta $carpeta, ?string $nombreSubcarpeta, string $nombreArchivo): void
    {
        $disk = $this->archivoService->disco();
        $base = $carpeta->url;
        if ($nombreSubcarpeta !== null && $nombreSubcarpeta !== '') {
            $sub = $carpeta->subcarpetas()->where('nombre', $nombreSubcarpeta)->first();
            $base = $sub?->url ?? $base.'/'.$nombreSubcarpeta;
        }
        $ruta = rtrim($base, '/').'/'.$nombreArchivo;
        if ($disk->exists($ruta)) {
            $disk->delete($ruta);
        }

        DocumentoCorporativo::query()
            ->where('carpeta_id', $carpeta->id)
            ->where('nombre_documento', $nombreArchivo)
            ->when(
                $nombreSubcarpeta !== null && $nombreSubcarpeta !== '',
                fn (Builder $q) => $q->where('subcarpeta', $nombreSubcarpeta),
                fn (Builder $q) => $q->whereNull('subcarpeta')
            )
            ->delete();
    }

    /**
     * @param  list<string>  $existentes
     * @param  list<string>  $deseo
     */
    public function sincronizarArchivosRaiz(Carpeta $carpeta, array $existentes, array $deseo, Empresa $empresa, ?User $usuario): void
    {
        $disk = $this->archivoService->disco();
        $aEliminar = array_diff($existentes, $deseo);
        foreach ($aEliminar as $ruta) {
            $nombre = basename((string) $ruta);
            if ($disk->exists($ruta)) {
                $disk->delete($ruta);
            }
            $this->eliminarRegistrosDocumentoCorporativo($carpeta->id, null, $nombre);
        }

        $aAgregar = array_diff($deseo, $existentes);
        if ($aAgregar !== []) {
            $this->registrarArchivosSubidosYNotificar($carpeta, $empresa, null, array_values($aAgregar), $usuario);
        }
    }

    /**
     * @param  list<string>  $existentes
     * @param  list<string>  $deseo
     */
    public function sincronizarArchivosSubcarpeta(
        Subcarpeta $subcarpeta,
        Carpeta $carpeta,
        Empresa $empresa,
        array $existentes,
        array $deseo,
        ?User $usuario,
    ): void {
        $disk = $this->archivoService->disco();
        $aEliminar = array_diff($existentes, $deseo);
        foreach ($aEliminar as $ruta) {
            $nombre = basename((string) $ruta);
            if ($disk->exists($ruta)) {
                $disk->delete($ruta);
            }
            $this->eliminarRegistrosDocumentoCorporativo($carpeta->id, $subcarpeta->nombre, $nombre);
        }

        $aAgregar = array_diff($deseo, $existentes);
        if ($aAgregar !== []) {
            $this->registrarArchivosSubidosYNotificar($carpeta, $empresa, $subcarpeta, array_values($aAgregar), $usuario);
        }
    }

    // --- Internos ---

    protected function eliminarRegistrosDocumentoCorporativo(int $carpetaId, ?string $subcarpeta, string $nombreArchivo): void
    {
        DocumentoCorporativo::query()
            ->where('carpeta_id', $carpetaId)
            ->where('nombre_documento', $nombreArchivo)
            ->when(
                $subcarpeta !== null && $subcarpeta !== '',
                fn (Builder $q) => $q->where('subcarpeta', $subcarpeta),
                fn (Builder $q) => $q->whereNull('subcarpeta')
            )
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    protected function validarSeleccionCatalogos(array $datos): void
    {
        $errors = [];
        if ($this->normalizarIds($datos['ubicacion_ids'] ?? []) === []) {
            $errors['ubicacion_ids'] = ['Seleccione al menos una ubicación.'];
        }
        if ($this->normalizarIds($datos['departamento_ids'] ?? []) === []) {
            $errors['departamento_ids'] = ['Seleccione al menos un departamento.'];
        }
        if ($this->normalizarIds($datos['area_ids'] ?? []) === []) {
            $errors['area_ids'] = ['Seleccione al menos un área.'];
        }
        if ($this->normalizarIds($datos['puesto_ids'] ?? []) === []) {
            $errors['puesto_ids'] = ['Seleccione al menos un puesto.'];
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<int|string>  $ids
     * @return list<int>
     */
    protected function normalizarIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $id): int => (int) $id,
            $ids
        ), static fn (int $id): bool => $id > 0)));
    }

    /**
     * @param  list<array{nombre?: string|null}>  $filas
     * @return list<string>
     */
    protected function extraerNombresSubcarpetas(array $filas): array
    {
        $out = [];
        foreach ($filas as $fila) {
            $n = trim((string) ($fila['nombre'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }

        return array_values(array_unique($out));
    }

    protected function existeCarpetaMismoNombre(int $empresaId, string $nombre, ?int $exceptoId = null): bool
    {
        $q = Carpeta::query()
            ->where('empresa_id', $empresaId)
            ->where('nombre', $nombre)
            ->where('tipo', Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS);

        if ($exceptoId !== null) {
            $q->where('id', '!=', $exceptoId);
        }

        return $q->exists();
    }

    /**
     * BL: Genera la ruta física de la carpeta usando el ID de la empresa:
     *     companies/{empresa-id}/documentos-corporativos/{slug-carpeta}
     * Si la ruta ya existe en el disco, agrega sufijo incremental (-1, -2, …).
     */
    protected function resolverRutaCarpetaFisica(Empresa $empresa, string $nombreMostrado): string
    {
        $segmento = $this->segmentoDirectorioSeguro($nombreMostrado);
        $base = "companies/{$empresa->id}/documentos-corporativos/{$segmento}";
        $disk = $this->archivoService->disco();
        $ruta = $base;
        $i = 0;
        while ($disk->exists($ruta)) {
            $i++;
            $ruta = $base.'-'.$i;
        }

        return $ruta;
    }

    protected function segmentoDirectorioSeguro(string $nombre): string
    {
        $slug = Str::slug($nombre);
        if ($slug === '') {
            $slug = 'carpeta';
        }

        return $slug;
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @param  list<int>  $departamentoIds
     * @param  list<int>  $areaIds
     * @param  list<int>  $puestoIds
     */
    protected function sincronizarPivotes(
        Carpeta $carpeta,
        int $empresaId,
        array $ubicacionIds,
        array $departamentoIds,
        array $areaIds,
        array $puestoIds,
    ): void {
        $carpeta->ubicaciones()->sync($ubicacionIds);
        $carpeta->departamentos()->sync($departamentoIds);
        $carpeta->areas()->sync($areaIds);
        $carpeta->puestos()->sync($puestoIds);
        $carpeta->empresasPivot()->sync([$empresaId]);
    }

    protected function crearSubcarpetaInterno(Carpeta $carpeta, string $nombreSub): Subcarpeta
    {
        $nombreSub = trim($nombreSub);
        if ($nombreSub === '') {
            throw ValidationException::withMessages(['subcarpetas' => ['Nombre de subcarpeta no válido.']]);
        }

        if ($carpeta->subcarpetas()->where('nombre', $nombreSub)->exists()) {
            throw ValidationException::withMessages(['subcarpetas' => ['Ya existe la subcarpeta: '.$nombreSub]]);
        }

        $segmento = $this->segmentoDirectorioSeguro($nombreSub);
        $url = $carpeta->url.'/'.$segmento;
        $disk = $this->archivoService->disco();
        if (! $disk->exists($url)) {
            $disk->makeDirectory($url);
        }

        return Subcarpeta::query()->create([
            'carpeta_id' => $carpeta->id,
            'nombre' => $nombreSub,
            'url' => $url,
            'tipo' => Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS,
        ]);
    }

    protected function moverArchivosStagingARaiz(string $stagingId, string $rutaDestino, string $subcarpetaStaging): void
    {
        $local = Storage::disk('local');
        $uploads = $this->archivoService->disco();
        $prefijo = 'tmp/carpetas-staging/'.$stagingId.'/'.$subcarpetaStaging;
        if (! $local->exists($prefijo)) {
            return;
        }
        foreach ($local->files($prefijo) as $archivo) {
            $nombreOriginal = basename($archivo);
            $nombreDestino = NombreArchivoDocumentosCorporativos::normalizarYAsegurarUnicoEnDirectorio(
                $uploads,
                $rutaDestino,
                $nombreOriginal
            );
            $contenido = $local->get($archivo);
            $uploads->put($rutaDestino.'/'.$nombreDestino, $contenido);
            $local->delete($archivo);
        }
    }

    /**
     * @param  list<array{nombre?: string|null, archivos?: mixed}>  $filasSubcarpetas
     */
    protected function moverArchivosStagingSubcarpetas(string $stagingId, Carpeta $carpeta, array $filasSubcarpetas): void
    {
        $local = Storage::disk('local');
        $carpeta->loadMissing('subcarpetas');
        foreach ($filasSubcarpetas as $fila) {
            $nombre = trim((string) ($fila['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $slug = $this->segmentoDirectorioSeguro($nombre);
            $prefijo = 'tmp/carpetas-staging/'.$stagingId.'/subs/'.$slug;
            if (! $local->exists($prefijo)) {
                continue;
            }
            $sub = $carpeta->subcarpetas->firstWhere('nombre', $nombre);
            if ($sub === null) {
                continue;
            }
            foreach ($local->files($prefijo) as $archivo) {
                $nombreOriginal = basename($archivo);
                $nombreDestino = NombreArchivoDocumentosCorporativos::normalizarYAsegurarUnicoEnDirectorio(
                    $this->archivoService->disco(),
                    $sub->url,
                    $nombreOriginal
                );
                $contenido = $local->get($archivo);
                $this->archivoService->disco()->put($sub->url.'/'.$nombreDestino, $contenido);
                $local->delete($archivo);
            }
        }
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @param  list<int>  $departamentoIds
     * @param  list<int>  $areaIds
     * @param  list<int>  $puestoIds
     */
    protected function notificarCarpetaCreada(
        Empresa $empresa,
        Carpeta $carpeta,
        array $ubicacionIds,
        array $departamentoIds,
        array $areaIds,
        array $puestoIds,
        ?User $usuario,
    ): void {
        $mensaje = $empresa->nombre.' ha publicado una nueva carpeta de documentos corporativos.';
        $this->dispararNotificacionPushDocumento(
            $empresa,
            $mensaje,
            $ubicacionIds,
            $departamentoIds,
            $areaIds,
            $puestoIds,
            [
                'tipo' => 'documentos_corporativos',
                'evento' => 'carpeta_creada',
                'carpeta_id' => $carpeta->id,
            ],
            $usuario,
            'Nueva carpeta de documentos'
        );
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @param  list<int>  $departamentoIds
     * @param  list<int>  $areaIds
     * @param  list<int>  $puestoIds
     */
    protected function notificarSubcarpetaCreada(
        Empresa $empresa,
        Carpeta $carpeta,
        Subcarpeta $subcarpeta,
        array $ubicacionIds,
        array $departamentoIds,
        array $areaIds,
        array $puestoIds,
        ?User $usuario,
    ): void {
        $mensaje = $empresa->nombre.' ha publicado una subcarpeta en documentos corporativos.';
        $this->dispararNotificacionPushDocumento(
            $empresa,
            $mensaje,
            $ubicacionIds,
            $departamentoIds,
            $areaIds,
            $puestoIds,
            [
                'tipo' => 'documentos_corporativos',
                'evento' => 'subcarpeta_creada',
                'carpeta_id' => $carpeta->id,
                'subcarpeta_id' => $subcarpeta->id,
            ],
            $usuario,
            'Nueva subcarpeta de documentos'
        );
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @param  list<int>  $departamentoIds
     * @param  list<int>  $areaIds
     * @param  list<int>  $puestoIds
     * @param  array<string, mixed>  $data
     */
    protected function dispararNotificacionPushDocumento(
        Empresa $empresa,
        string $mensaje,
        array $ubicacionIds,
        array $departamentoIds,
        array $areaIds,
        array $puestoIds,
        array $data,
        ?User $usuario,
        string $titulo = 'Documentos corporativos',
    ): void {
        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $empresa->id,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => null,
            'data' => $data,
            'filtros' => [
                'ubicaciones' => $ubicacionIds,
                'departamentos' => $departamentoIds,
                'areas' => $areaIds,
                'puestos' => $puestoIds,
            ],
            'estado' => EstadoNotificacionPush::BORRADOR,
            'creado_por' => $usuario?->id,
        ]);

        $this->resolverDestinatarios->persistirDestinatarios($notificacion);
        $this->enviarNotificacionPushAction->enviarAhora($notificacion);
    }

    /**
     * @param  list<int>  $ubicacionIds
     * @param  list<int>  $departamentoIds
     * @param  list<int>  $areaIds
     * @param  list<int>  $puestoIds
     * @return Collection<int, int>
     */
    protected function carpetaTieneAlgunArchivoEnDisco(Carpeta $carpeta): bool
    {
        $disk = $this->archivoService->disco();
        $carpeta->loadMissing('subcarpetas');
        if ($disk->exists($carpeta->url) && $disk->files($carpeta->url) !== []) {
            return true;
        }
        foreach ($carpeta->subcarpetas as $sub) {
            if ($disk->exists($sub->url) && $disk->files($sub->url) !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * BL: Tras crear la carpeta y mover archivos del staging, genera filas documentos_corporativos
     * y un envío push agrupado (equivalente a subir archivos en el legacy).
     */
    protected function registrarDocumentosInicialesEnDisco(Carpeta $carpeta, Empresa $empresa, ?User $usuario): void
    {
        $disk = $this->archivoService->disco();
        $carpeta->loadMissing('subcarpetas');

        $raiz = $disk->exists($carpeta->url) ? $disk->files($carpeta->url) : [];
        if ($raiz !== []) {
            $this->registrarArchivosSubidosYNotificar($carpeta, $empresa, null, $raiz, $usuario);
        }

        foreach ($carpeta->subcarpetas as $sub) {
            $archivos = $disk->exists($sub->url) ? $disk->files($sub->url) : [];
            if ($archivos !== []) {
                $this->registrarArchivosSubidosYNotificar($carpeta, $empresa, $sub, $archivos, $usuario);
            }
        }
    }

    /**
     * BL: Retorna IDs de User (no Colaborador) que cumplen los filtros de catálogos RH.
     * Los filtros se aplican vía whereHas('colaborador') siguiendo la arquitectura users-first.
     */
    protected function userIdsFiltrados(
        Empresa $empresa,
        array $ubicacionIds,
        array $departamentoIds,
        array $areaIds,
        array $puestoIds,
    ): Collection {
        return User::query()
            ->where('empresa_id', $empresa->id)
            ->whereNull('deleted_at')
            ->whereHas('colaborador', function (Builder $q) use ($ubicacionIds, $departamentoIds, $areaIds, $puestoIds): void {
                $q->whereNull('deleted_at');

                if ($ubicacionIds !== []) {
                    $q->whereIn('ubicacion_id', $ubicacionIds);
                }
                if ($departamentoIds !== []) {
                    $q->whereIn('departamento_id', $departamentoIds);
                }
                if ($areaIds !== []) {
                    $q->whereIn('area_id', $areaIds);
                }
                if ($puestoIds !== []) {
                    $q->whereIn('puesto_id', $puestoIds);
                }
            })
            ->pluck('id');
    }
}
