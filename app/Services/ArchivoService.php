<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoService
{
    public function disco(): Filesystem
    {
        return Storage::disk($this->nombreDisco());
    }

    public function nombreDisco(): string
    {
        return config('filesystems.archivos_disk', 'uploads');
    }

    /**
     * Guardar archivo desde upload (Livewire o Request).
     *
     * @return string Ruta relativa del archivo guardado
     */
    public function guardar(
        UploadedFile $archivo,
        Empresa|int|string $empresa,
        string $modulo,
        int|string $registroId,
        ?string $nombre = null,
        ?string $extension = null,
    ): string {
        $empresaId = $this->resolverEmpresaId($empresa);
        $directorio = $this->construirDirectorio($empresaId, $modulo, $registroId);

        $extension = $extension ?? $archivo->getClientOriginalExtension();
        $nombre = $nombre ?? pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $nombre = Str::slug($nombre);
        $nombreFinal = "{$nombre}.{$extension}";

        $this->disco()->putFileAs($directorio, $archivo, $nombreFinal);

        return "{$directorio}/{$nombreFinal}";
    }

    /**
     * Guardar contenido raw (archivos generados: PDF, Excel, imágenes procesadas, etc.)
     *
     * @return string Ruta relativa del archivo guardado
     */
    public function guardarContenido(
        string $contenido,
        Empresa|int|string $empresa,
        string $modulo,
        int|string $registroId,
        string $nombreConExtension,
    ): string {
        $empresaId = $this->resolverEmpresaId($empresa);
        $directorio = $this->construirDirectorio($empresaId, $modulo, $registroId);
        $rutaCompleta = "{$directorio}/{$nombreConExtension}";

        $this->disco()->put($rutaCompleta, $contenido);

        return $rutaCompleta;
    }

    public function eliminar(string $ruta): bool
    {
        if ($this->existe($ruta)) {
            return $this->disco()->delete($ruta);
        }

        return false;
    }

    public function existe(string $ruta): bool
    {
        return $this->disco()->exists($ruta);
    }

    /**
     * Obtener URL del archivo.
     * S3: URL firmada temporal. Local: asset().
     */
    public function url(string $ruta, int $minutos = 60): string
    {
        if (! $this->existe($ruta)) {
            return '';
        }

        if ($this->nombreDisco() === 's3') {
            return $this->disco()->temporaryUrl($ruta, now()->addMinutes($minutos));
        }

        return asset($ruta);
    }

    /**
     * URL permanente (solo para archivos que deben ser públicos).
     */
    public function urlPermanente(string $ruta): string
    {
        if (! $this->existe($ruta)) {
            return '';
        }

        return $this->disco()->url($ruta);
    }

    public function mover(string $origen, string $destino): bool
    {
        if (! $this->existe($origen)) {
            return false;
        }

        return $this->disco()->move($origen, $destino);
    }

    public function copiar(string $origen, string $destino): bool
    {
        if (! $this->existe($origen)) {
            return false;
        }

        return $this->disco()->copy($origen, $destino);
    }

    /**
     * @return list<string>
     */
    public function listar(string $directorio): array
    {
        return $this->disco()->files($directorio);
    }

    /**
     * @return list<string>
     */
    public function listarDirectorios(string $directorio): array
    {
        return $this->disco()->directories($directorio);
    }

    public function obtener(string $ruta): ?string
    {
        if (! $this->existe($ruta)) {
            return null;
        }

        return $this->disco()->get($ruta);
    }

    public function descargar(string $ruta, ?string $nombreDescarga = null): StreamedResponse
    {
        $nombreDescarga = $nombreDescarga ?? basename($ruta);

        return $this->disco()->download($ruta, $nombreDescarga);
    }

    public function eliminarDirectorio(string $directorio): bool
    {
        return $this->disco()->deleteDirectory($directorio);
    }

    public function construirDirectorio(
        string|int $empresaId,
        string $modulo,
        int|string $registroId,
    ): string {
        return "companies/{$empresaId}/{$modulo}/{$registroId}";
    }

    public function construirRuta(
        string|int $empresaId,
        string $modulo,
        int|string $registroId,
        string $nombreArchivo,
    ): string {
        return "companies/{$empresaId}/{$modulo}/{$registroId}/{$nombreArchivo}";
    }

    /**
     * BL: Las carpetas de empresas en Wasabi/S3 se identifican por ID, no por slug.
     * Así lo maneja el legacy y es más estable ante cambios de nombre de empresa.
     */
    protected function resolverEmpresaId(Empresa|int|string $empresa): string
    {
        if ($empresa instanceof Empresa) {
            return (string) $empresa->id;
        }

        if (is_int($empresa)) {
            return (string) $empresa;
        }

        return $empresa;
    }
}
