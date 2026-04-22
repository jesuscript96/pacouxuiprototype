<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Schemas;

use App\Models\CategoriaSolicitud;
use App\Services\ArchivoService;
use App\Services\TipoSolicitudAutorizacionOpciones;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Formulario de permisos (modelo {@see \App\Models\TipoSolicitud}) para el panel Cliente.
 *
 * Layout en 3 secciones: datos del permiso, etapas de autorización
 * y preguntas opcionales con imágenes en Wasabi/S3.
 *
 * @param  ?int  $tipoSolicitudIdParaDirectorioUploads  En edición: ID del permiso; las imágenes nuevas van
 *                                                      directo a `companies/{empresa}/tipos-solicitud/{id}/`
 *                                                      (sin `tmp`). En alta: null → carpeta `tmp` hasta guardar.
 */
class PermisoForm
{
    private static function seccionDatosPermiso(?int $tenantId): Section
    {
        return Section::make('Datos del permiso')
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(191),
                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ])
                    ->required()
                    ->default(true),
                Select::make('rango_fechas')
                    ->label('Rango de fechas')
                    ->options([
                        'PASADAS' => 'Pasadas',
                        'FUTURAS' => 'Futuras',
                        'AMBAS' => 'Ambas',
                    ])
                    ->required(),
                Select::make('unidad_tiempo')
                    ->label('Tiempo del permiso')
                    ->options([
                        'DIAS' => 'Días',
                        'HORAS' => 'Horas',
                    ])
                    ->required(),
                Toggle::make('tiene_vigencia')
                    ->label('Agregar vigencia')
                    ->helperText('Permite definir una fecha límite para la solicitud.')
                    ->live(),
                DatePicker::make('fecha_vigencia')
                    ->label('Fecha de vigencia')
                    ->visible(fn (Get $get): bool => (bool) $get('tiene_vigencia')),
                Select::make('categoria_solicitud_id')
                    ->label('Categoría')
                    ->options(function () use ($tenantId): array {
                        if ($tenantId === null) {
                            return [];
                        }

                        return CategoriaSolicitud::query()
                            ->where(function ($q) use ($tenantId): void {
                                $q->whereNull('empresa_id')
                                    ->orWhere('empresa_id', $tenantId);
                            })
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required(),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    private static function seccionEtapas(?int $tenantId): Section
    {
        return Section::make('Etapas de autorización')
            ->description('Define quién autoriza las solicitudes de este tipo. Cada etapa puede ser por persona específica o por nivel jerárquico.')
            ->schema([
                Repeater::make('etapas')
                    ->label('')
                    ->schema([
                        Select::make('nivel_autorizacion')
                            ->label('Modalidad')
                            ->options([
                                'POR NOMBRE' => 'Por nombre',
                                'JERARQUIA' => 'Jerarquía',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                $set('usuarios', []);
                                $set('niveles_jerarquia', []);
                            }),
                        Select::make('usuarios')
                            ->label('Autorizadores')
                            ->helperText('Solo aparecen usuarios registrados como autorizadores en la empresa.')
                            ->multiple()
                            ->searchable()
                            ->options(function () use ($tenantId): array {
                                if ($tenantId === null) {
                                    return [];
                                }

                                return TipoSolicitudAutorizacionOpciones::opcionesAutorizadoresPorNombre((int) $tenantId);
                            })
                            ->visible(fn (Get $get): bool => $get('nivel_autorizacion') === 'POR NOMBRE')
                            ->required(fn (Get $get): bool => $get('nivel_autorizacion') === 'POR NOMBRE'),

                        Select::make('niveles_jerarquia')
                            ->label('Niveles de jerarquía')
                            ->helperText(function () use ($tenantId): ?string {
                                if ($tenantId === null) {
                                    return null;
                                }
                                $opts = TipoSolicitudAutorizacionOpciones::opcionesNivelesJerarquia((int) $tenantId);

                                return $opts === []
                                    ? 'No hay niveles jerárquicos configurados. Contacte al administrador.'
                                    : null;
                            })
                            ->multiple()
                            ->options(function () use ($tenantId): array {
                                if ($tenantId === null) {
                                    return [];
                                }

                                return TipoSolicitudAutorizacionOpciones::opcionesNivelesJerarquia((int) $tenantId);
                            })
                            ->visible(fn (Get $get): bool => $get('nivel_autorizacion') === 'JERARQUIA')
                            ->required(fn (Get $get): bool => $get('nivel_autorizacion') === 'JERARQUIA'),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Agregar etapa')
                    ->reorderable()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    /**
     * @see \App\Services\TipoSolicitudPersistService Reubica imágenes desde tmp al guardar (alta).
     */
    private static function seccionPreguntas(ArchivoService $archivoService, ?int $tenantId, ?int $tipoSolicitudIdParaDirectorioUploads): Section
    {
        return Section::make('Preguntas de la solicitud')
            ->description('Opcional. Tipos: abierta, opción múltiple o selección múltiple.')
            ->schema([
                Repeater::make('preguntas')
                    ->label('')
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'open_question' => 'Pregunta abierta',
                                'multiple_option' => 'Opción múltiple',
                                'multiple_choice' => 'Selección múltiple',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('numero')
                            ->label('Número')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999)
                            ->required(),
                        TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('subtitulo')
                            ->label('Subtítulo')
                            ->maxLength(300),

                        FileUpload::make('imagen')
                            ->label('Imagen')
                            ->image()
                            ->disk($archivoService->nombreDisco())
                            ->directory(function () use ($tenantId, $tipoSolicitudIdParaDirectorioUploads): string {
                                $empresa = (int) ($tenantId ?? 0);
                                if ($tipoSolicitudIdParaDirectorioUploads !== null && $tipoSolicitudIdParaDirectorioUploads > 0) {
                                    return 'companies/'.$empresa.'/tipos-solicitud/'.$tipoSolicitudIdParaDirectorioUploads;
                                }

                                return 'companies/'.$empresa.'/tipos-solicitud/tmp';
                            })
                            ->visibility('public')
                            ->fetchFileInformation(false)
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get): string {
                                $numero = (int) ($get('numero') ?? 0);

                                return 'imagen_'.$numero.'_'.time().'.'.$file->getClientOriginalExtension();
                            })
                            ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames) use ($archivoService): ?array {
                                $ruta = ltrim(str_replace('\\', '/', $file), '/');
                                if ($ruta === '') {
                                    return null;
                                }

                                $disco = $archivoService->disco();
                                $discoNombre = $archivoService->nombreDisco();

                                $url = $discoNombre === 's3'
                                    ? $disco->temporaryUrl($ruta, now()->addMinutes(60))
                                    : asset($ruta);

                                $nombre = $component->isMultiple()
                                    ? ($storedFileNames[$file] ?? null)
                                    : $storedFileNames;

                                return [
                                    'name' => $nombre ?? basename($ruta),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url' => $url,
                                ];
                            })
                            ->nullable(),

                        Hidden::make('imagen_actual'),

                        TextInput::make('min_respuestas')
                            ->label('Selección mínima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->visible(fn (Get $get): bool => $get('tipo') === 'multiple_choice')
                            ->required(fn (Get $get): bool => $get('tipo') === 'multiple_choice'),
                        TextInput::make('max_respuestas')
                            ->label('Selección máxima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->visible(fn (Get $get): bool => $get('tipo') === 'multiple_choice')
                            ->required(fn (Get $get): bool => $get('tipo') === 'multiple_choice'),
                        Repeater::make('opciones')
                            ->label('Respuestas')
                            ->schema([
                                TextInput::make('titulo')
                                    ->label('Texto')
                                    ->required(),
                            ])
                            ->visible(fn (Get $get): bool => in_array($get('tipo'), ['multiple_option', 'multiple_choice'], true))
                            ->defaultItems(1)
                            ->addActionLabel('Agregar respuesta')
                            ->collapsible(),
                        TextInput::make('texto_personalizado')
                            ->label('Respuesta personalizada (opcional)')
                            ->maxLength(300)
                            ->visible(fn (Get $get): bool => in_array($get('tipo'), ['multiple_option', 'multiple_choice'], true)),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Agregar pregunta')
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    public static function configure(Schema $schema, ?int $tipoSolicitudIdParaDirectorioUploads = null): Schema
    {
        $tenantId = Filament::getTenant()?->id;
        $archivoService = app(ArchivoService::class);

        return $schema
            ->components([
                static::seccionDatosPermiso($tenantId),
                static::seccionEtapas($tenantId),
                static::seccionPreguntas($archivoService, $tenantId, $tipoSolicitudIdParaDirectorioUploads),
            ]);
    }
}
