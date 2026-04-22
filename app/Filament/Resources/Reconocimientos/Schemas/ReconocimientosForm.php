<?php

namespace App\Filament\Resources\Reconocimientos\Schemas;

use App\Models\Empresa;
use App\Models\Reconocmiento;
use App\Services\ArchivoService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ReconocimientosForm
{
    private const string MODULO = 'reconocimientos';

    /**
     * Construye un campo FileUpload configurado para almacenar imágenes
     * de reconocimientos en Wasabi/S3 vía ArchivoService.
     *
     * @param  string  $campo  Nombre del campo en el modelo (imagen_inicial o imagen_final)
     * @param  string  $label  Etiqueta visible en el formulario
     */
    private static function campoImagen(ArchivoService $archivoService, string $campo, string $label): FileUpload
    {
        return FileUpload::make($campo)
            ->label($label)
            ->image()
            ->disk($archivoService->nombreDisco())
            ->directory(self::MODULO)
            ->fetchFileInformation(false)
            ->maxSize(5480)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/bmp'])
            // Nombre del archivo en disco: {campo}_{timestamp}.{ext}
            // @see \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($campo): string {
                return $campo.'_'.time().'.'.$file->getClientOriginalExtension();
            })
            // Carga la metadata de un archivo ya existente en disco (al editar).
            // Genera la URL firmada (S3) o asset (local) sin hacer HEAD requests.
            ->getUploadedFileUsing(function (mixed $component, string $file, mixed $storedFileNames) use ($archivoService): ?array {
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
            ->helperText('Formatos: jpg, jpeg, png, bmp. Máximo 5MB.');
    }

    /**
     * Configura el formulario completo de Reconocimiento.
     *
     * Sección única con: nombre, descripción, toggles de envío/exclusividad,
     * menciones necesarias, selector de empresas (solo si es exclusivo),
     * y campos de imagen inicial/final almacenados en Wasabi/S3.
     */
    public static function configure(Schema $schema): Schema
    {
        $archivoService = app(ArchivoService::class);

        return $schema
            ->components([
                Section::make('Información general')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->unique(Reconocmiento::class)
                            ->maxLength(150),
                        TextInput::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('es_enviable')
                            ->label('Disponible para enviar')
                            ->default(false),
                        Toggle::make('es_exclusivo')
                            ->label('Es exclusivo')
                            ->live()
                            ->default(false),

                        TextInput::make('menciones_necesarias')
                            ->label('Menciones necesarias')
                            ->required()
                            ->numeric()
                            ->rule('integer')
                            ->step(1)
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(999),

                        // Selector de empresas, solo habilitado cuando es_exclusivo está activo.
                        // Si no es exclusivo, se asigna a TODAS las empresas en el afterCreate/afterSave.
                        Select::make('empresas')
                            ->label('Empresas (solo si es exclusivo)')
                            ->multiple()
                            ->options(fn (): array => Cache::remember(
                                'filament_reconocimientos_empresas',
                                300,
                                fn () => Empresa::query()->orderBy('nombre')->pluck('nombre', 'id')->toArray()
                            ))
                            ->disabled(fn (Get $get): bool => ! (bool) $get('es_exclusivo')),

                        static::campoImagen($archivoService, 'imagen_inicial', 'Imagen inicial'),
                        static::campoImagen($archivoService, 'imagen_final', 'Imagen final'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
