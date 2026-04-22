<?php

namespace App\Filament\Resources\GestionCarruseles\Pages;

use App\Filament\Resources\GestionCarruseles\GestionCarruselesResource;
use App\Models\Carrusel;
use App\Models\Log;
use App\Services\ArchivoService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditCarruselEmpresa extends EditRecord
{
    protected static string $resource = GestionCarruselesResource::class;

    protected static int $maxImagenesCarrusel = 5;

    protected static string $moduloCarousel = 'carousel';

    public function getTitle(): string
    {
        return 'Carrusel de imágenes: '.$this->getRecord()->nombre;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver al listado')
                ->url(GestionCarruselesResource::getUrl('index')),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $record = $this->getRecord();
        $archivoService = app(ArchivoService::class);
        $directory = "companies/{$record->id}/".self::$moduloCarousel;

        return $schema
            ->components([
                Section::make('Imágenes del carrusel')
                    ->description('Máximo '.self::$maxImagenesCarrusel.' imágenes. Sube, reordena o elimina las que se muestran en el carrusel de la empresa.')
                    ->schema([
                        FileUpload::make('imagenes_carrusel')
                            ->label('Imágenes')
                            ->image()
                            ->multiple()
                            ->maxSize(5_100)
                            ->maxFiles(self::$maxImagenesCarrusel)
                            ->disk($archivoService->nombreDisco())
                            ->directory($directory)
                            ->visibility('public')
                            ->reorderable()
                            ->dehydrated(true)
                            ->fetchFileInformation(false)
                            ->getUploadedFileUsing(function (mixed $component, string $file, mixed $storedFileNames) use ($archivoService): ?array {
                                // BL: No hacemos HEAD requests a S3 (existe/size/mimeType) para evitar
                                // latencia al cargar la página. Confiamos en la BD como fuente de verdad.
                                $disco = $archivoService->disco();
                                $discoNombre = $archivoService->nombreDisco();

                                $url = $discoNombre === 's3'
                                    ? $disco->temporaryUrl($file, now()->addMinutes(60))
                                    : asset($file);

                                return [
                                    'name' => basename($file),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url' => $url,
                                ];
                            })
                            ->helperText('Formatos: imagen. Máximo '.self::$maxImagenesCarrusel.' archivos.'),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // BL: orderByDesc para que el componente FileUpload los muestre en orden visual correcto
        $data['imagenes_carrusel'] = $record->carruseles()
            ->orderByDesc('orden')
            ->pluck('ruta')
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $paths = $data['imagenes_carrusel'] ?? [];
        if (is_array($paths) && count($paths) > self::$maxImagenesCarrusel) {
            throw ValidationException::withMessages([
                'imagenes_carrusel' => ['Solo están permitidas '.self::$maxImagenesCarrusel.' imágenes por carrusel.'],
            ]);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $archivoService = app(ArchivoService::class);
        // BL: FileUpload con reorderable() devuelve el array en orden inverso al visual
        $finalPaths = array_values(array_reverse($data['imagenes_carrusel'] ?? []));

        $existingCarruseles = $record->carruseles()->get();
        $existingPaths = $existingCarruseles->pluck('ruta')->all();

        $toDelete = array_diff($existingPaths, $finalPaths);
        foreach ($existingCarruseles as $carrusel) {
            if (in_array($carrusel->ruta, $toDelete)) {
                $archivoService->eliminar($carrusel->ruta);
                $carrusel->delete();
            }
        }

        foreach ($finalPaths as $orden => $ruta) {
            Carrusel::query()->updateOrCreate(
                [
                    'empresa_id' => $record->id,
                    'ruta' => $ruta,
                ],
                [
                    'nombre_archivo' => basename($ruta),
                    'orden' => $orden,
                ],
            );
        }

        $user = auth()->user();
        if ($user) {
            Log::create([
                'accion' => 'El usuario '.$user->name.' / '.$user->email.' ha modificado el carrusel de imágenes para la empresa: '.$record->id.' ('.$record->nombre.')',
                'fecha' => now(),
                'user_id' => $user->id,
                'empresa_id' => $record->id,
            ]);
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Carrusel de imágenes actualizado correctamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
