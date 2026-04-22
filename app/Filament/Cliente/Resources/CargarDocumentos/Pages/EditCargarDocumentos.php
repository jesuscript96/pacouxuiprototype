<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos\Pages;

use App\Filament\Cliente\Resources\CargarDocumentos\CargarDocumentosResource;
use App\Filament\Cliente\Resources\CargarDocumentos\Schemas\CarpetaDocumentosWizard;
use App\Models\Carpeta;
use App\Models\Empresa;
use App\Services\ArchivoService;
use App\Services\DocumentosCorporativosCarpetaService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EditCargarDocumentos extends EditRecord
{
    protected static string $resource = CargarDocumentosResource::class;

    protected static ?string $request = null;

    public function getTitle(): string
    {
        return 'Editar carpeta: '.$this->record->nombre;
    }

    public function form(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant instanceof Empresa, 403);
        /** @var Carpeta $record */
        $record = $this->record;

        return CarpetaDocumentosWizard::configure($schema, $tenant, null, $record);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Carpeta $record */
        $record = $this->record;
        $record->load(['ubicaciones', 'departamentos', 'areas', 'puestos', 'subcarpetas']);

        $data['ubicacion_ids'] = $record->ubicaciones->pluck('id')->all();
        $data['departamento_ids'] = $record->departamentos->pluck('id')->all();
        $data['area_ids'] = $record->areas->pluck('id')->all();
        $data['puesto_ids'] = $record->puestos->pluck('id')->all();
        $data['nombre'] = $record->nombre;

        /** @var ArchivoService $archivoSvc */
        $archivoSvc = app(ArchivoService::class);
        $disk = $archivoSvc->disco();

        $data['archivos_raiz'] = $disk->exists($record->url) ? $disk->files($record->url) : [];
        $data['archivos_por_subcarpeta'] = $record->subcarpetas->map(fn ($s): array => [
            'subcarpeta_id' => $s->id,
            'nombre' => $s->nombre,
            'directorio' => $s->url,
            'archivos' => $disk->exists($s->url) ? $disk->files($s->url) : [],
        ])->values()->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant instanceof Empresa, 403);
        abort_unless($record instanceof Carpeta, 404);

        $service = app(DocumentosCorporativosCarpetaService::class);

        /** @var ArchivoService $archivoSvc */
        $archivoSvc = app(ArchivoService::class);
        $disk = $archivoSvc->disco();

        $archivosRaizAntes = $disk->exists($record->url) ? $disk->files($record->url) : [];

        /** @var Collection<int, list<string>> $archivosSubAntes */
        $archivosSubAntes = collect();
        $record->load('subcarpetas');
        foreach ($record->subcarpetas as $sub) {
            $archivosSubAntes[$sub->id] = $disk->exists($sub->url) ? $disk->files($sub->url) : [];
        }

        $service->actualizarCarpeta($record, $tenant, auth()->user(), $data);

        $record->refresh();
        $record->load('subcarpetas');

        $deseoRaiz = $data['archivos_raiz'] ?? [];
        if (! is_array($deseoRaiz)) {
            $deseoRaiz = [];
        }
        $service->sincronizarArchivosRaiz(
            $record,
            $archivosRaizAntes,
            $this->normalizarRutasUploads($deseoRaiz, $record->url, $archivoSvc),
            $tenant,
            auth()->user()
        );

        foreach ($data['archivos_por_subcarpeta'] ?? [] as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            $sid = (int) ($fila['subcarpeta_id'] ?? 0);
            $sub = $record->subcarpetas->firstWhere('id', $sid);
            if ($sub === null) {
                continue;
            }
            $deseo = $fila['archivos'] ?? [];
            if (! is_array($deseo)) {
                $deseo = [];
            }
            $antes = $archivosSubAntes->get($sid, []);
            $service->sincronizarArchivosSubcarpeta(
                $sub,
                $record,
                $tenant,
                is_array($antes) ? $antes : [],
                $this->normalizarRutasUploads($deseo, $sub->url, $archivoSvc),
                auth()->user()
            );
        }

        return $record->refresh();
    }

    /**
     * BL: Normaliza las rutas de archivos verificando existencia en el disco configurado (Wasabi/local).
     * Si un archivo no existe en la ruta dada, asume que está en la baseUrl de la carpeta.
     *
     * @param  list<string>  $rutas
     * @return list<string>
     */
    protected function normalizarRutasUploads(array $rutas, string $baseUrl, ArchivoService $archivoSvc): array
    {
        $disk = $archivoSvc->disco();

        return collect($rutas)
            ->map(function (mixed $p) use ($disk, $baseUrl): string {
                $p = (string) $p;
                if ($disk->exists($p)) {
                    return $p;
                }

                return rtrim($baseUrl, '/').'/'.basename($p);
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function getRedirectUrl(): string
    {
        return CargarDocumentosResource::getUrl('index', ['tenant' => Filament::getTenant()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cambios guardados.';
    }
}
