<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos\Schemas;

use App\Models\Area;
use App\Models\Carpeta;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Puesto;
use App\Models\Ubicacion;
use App\Services\ArchivoService;
use App\Support\NombreArchivoDocumentosCorporativos;
use Closure;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CarpetaDocumentosWizard
{
    /**
     * BL: Espacios → _, sin acentos, ñ→n; evita colisiones en el directorio de destino.
     *
     * @return Closure(BaseFileUpload, TemporaryUploadedFile): string
     */
    public static function closureNombreArchivoAlmacenamiento(): Closure
    {
        return function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
            return NombreArchivoDocumentosCorporativos::normalizarYAsegurarUnicoEnDirectorio(
                $component->getDisk(),
                (string) ($component->getDirectory() ?? ''),
                $file->getClientOriginalName()
            );
        };
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Wizard\Step>
     */
    public static function steps(Empresa $empresa, ?string $stagingId = null, ?Carpeta $carpeta = null): array
    {
        $empresaId = $empresa->id;
        $esEdicion = $carpeta instanceof Carpeta;
        $sidStaging = $stagingId ?? '';

        $pasoUbicacion = Step::make('Ubicación y alcance')
            ->description('Seleccione ubicación, departamento, área y puesto destino (filtros para colaboradores y notificaciones).')
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('ubicacion_ids')
                            ->label('Ubicaciones')
                            ->multiple()
                            ->options(fn (): array => Ubicacion::query()
                                ->where('empresa_id', $empresaId)
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('departamento_ids')
                            ->label('Departamentos')
                            ->multiple()
                            ->options(fn (): array => Departamento::query()
                                ->where('empresa_id', $empresaId)
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('area_ids')
                            ->label('Áreas')
                            ->multiple()
                            ->options(fn (): array => Area::query()
                                ->where('empresa_id', $empresaId)
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('puesto_ids')
                            ->label('Puestos')
                            ->multiple()
                            ->options(fn (): array => Puesto::query()
                                ->where('empresa_id', $empresaId)
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);

        /** @var ArchivoService $archivoSvc */
        $archivoSvc = app(ArchivoService::class);
        $discoNombre = $archivoSvc->nombreDisco();

        $seccionCarpetaPrincipal = $esEdicion && $carpeta instanceof Carpeta
            ? Section::make('Carpeta principal')
                ->description('Nombre de la carpeta y archivos en su raíz.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la carpeta')
                        ->required()
                        ->maxLength(191)
                        ->disabled()
                        ->dehydrated(true)
                        ->helperText('Para cambiar el nombre elimine la carpeta y cree una nueva (afecta rutas en disco).'),
                    FileUpload::make('archivos_raiz')
                        ->label('Archivos en la carpeta principal')
                        ->multiple()
                        ->disk($discoNombre)
                        ->directory($carpeta->url)
                        ->visibility('public')
                        ->getUploadedFileNameForStorageUsing(self::closureNombreArchivoAlmacenamiento())
                        ->getUploadedFileUsing(function (mixed $component, string $file, mixed $storedFileNames) use ($archivoSvc): ?array {
                            if (! $archivoSvc->existe($file)) {
                                return null;
                            }
                            $disk = $archivoSvc->disco();

                            return [
                                'name' => basename($file),
                                'size' => $disk->size($file),
                                'type' => $disk->mimeType($file),
                                'url' => $archivoSvc->url($file),
                            ];
                        })
                        ->helperText('Máximo 20 MB por archivo. El nombre se normaliza (sin acentos, espacios como _).'),
                ])
            : Section::make('Carpeta principal')
                ->description('Defina el nombre y, si desea, suba archivos en la raíz de la carpeta.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la carpeta')
                        ->required()
                        ->maxLength(191),
                    FileUpload::make('archivos_raiz')
                        ->label('Archivos en la carpeta principal (opcional)')
                        ->multiple()
                        ->disk('local')
                        ->directory('tmp/carpetas-staging/'.$sidStaging.'/raiz')
                        ->visibility('private')
                        ->getUploadedFileNameForStorageUsing(self::closureNombreArchivoAlmacenamiento())
                        ->maxSize(20480)
                        ->helperText('Máximo 20 MB por archivo. El nombre se normaliza al publicar la carpeta.'),
                ]);

        $subFilas = ($esEdicion && $carpeta instanceof Carpeta)
            ? $carpeta->loadMissing('subcarpetas')->subcarpetas->map(fn ($s): array => [
                'subcarpeta_id' => $s->id,
                'nombre' => $s->nombre,
                'directorio' => $s->url,
                'archivos' => $archivoSvc->existe($s->url)
                    ? $archivoSvc->disco()->files($s->url)
                    : [],
            ])->values()->all()
            : [];

        $seccionSubcarpetas = $esEdicion
            ? Section::make('Subcarpetas')
                ->description('Nuevas subcarpetas y archivos en las existentes.')
                ->schema([
                    Repeater::make('subcarpetas_nuevas')
                        ->label('Nuevas subcarpetas')
                        ->schema([
                            TextInput::make('nombre')
                                ->label('Nombre')
                                ->required()
                                ->maxLength(191),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Agregar subcarpeta')
                        ->columnSpanFull(),
                    Repeater::make('archivos_por_subcarpeta')
                        ->label('Archivos por subcarpeta existente')
                        ->default($subFilas)
                        ->visible(fn (): bool => $subFilas !== [])
                        ->schema([
                            Hidden::make('subcarpeta_id'),
                            Hidden::make('directorio'),
                            TextInput::make('nombre')
                                ->label('Subcarpeta')
                                ->disabled()
                                ->dehydrated(true),
                            FileUpload::make('archivos')
                                ->label('Archivos')
                                ->multiple()
                                ->disk($discoNombre)
                                ->directory(fn ($get): string => (string) $get('directorio'))
                                ->visibility('public')
                                ->getUploadedFileNameForStorageUsing(self::closureNombreArchivoAlmacenamiento())
                                ->getUploadedFileUsing(function (mixed $component, string $file, mixed $storedFileNames) use ($archivoSvc): ?array {
                                    if (! $archivoSvc->existe($file)) {
                                        return null;
                                    }
                                    $disk = $archivoSvc->disco();

                                    return [
                                        'name' => basename($file),
                                        'size' => $disk->size($file),
                                        'type' => $disk->mimeType($file),
                                        'url' => $archivoSvc->url($file),
                                    ];
                                }),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ])
            : Section::make('Subcarpetas')
                ->description('Opcional: agregue subcarpetas y archivos en cada una (indique el nombre antes de subir).')
                ->schema([
                    Repeater::make('subcarpetas')
                        ->label('')
                        ->schema([
                            TextInput::make('nombre')
                                ->label('Nombre de la subcarpeta')
                                ->required()
                                ->maxLength(191)
                                ->live(onBlur: true),
                            FileUpload::make('archivos')
                                ->label('Archivos en esta subcarpeta')
                                ->multiple()
                                ->required()
                                ->disk('local')
                                ->directory(function ($get) use ($sidStaging): string {
                                    $n = trim((string) $get('nombre'));
                                    $slug = Str::slug($n);

                                    return 'tmp/carpetas-staging/'.$sidStaging.'/subs/'.($slug !== '' ? $slug : 'sub-pendiente');
                                })
                                ->visibility('private')
                                ->getUploadedFileNameForStorageUsing(self::closureNombreArchivoAlmacenamiento())
                                ->maxSize(20480),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Agregar subcarpeta')
                        ->columnSpanFull(),
                ]);

        $pasoCarpetaYArchivos = Step::make('Carpeta y archivos')
            ->description($esEdicion
                ? 'Administre la carpeta principal, nuevas subcarpetas y archivos en cada una.'
                : 'Defina la carpeta principal, sus archivos y las subcarpetas con sus archivos.')
            ->schema([
                $seccionCarpetaPrincipal,
                $seccionSubcarpetas,
            ]);

        return [$pasoUbicacion, $pasoCarpetaYArchivos];
    }

    public static function configure(Schema $schema, Empresa $empresa, ?string $stagingId = null, ?Carpeta $carpeta = null): Schema
    {
        $carpetaParaPasos = $carpeta?->loadMissing('subcarpetas');

        $esCreacion = $stagingId !== null;

        $wizard = Wizard::make(self::steps($empresa, $stagingId, $carpetaParaPasos))
            ->columnSpanFull();

        // BL: En creación, el botón Crear solo aparece en el último paso del wizard
        if ($esCreacion) {
            $wizard->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                <x-filament::button type="submit" size="sm">
                    Crear
                </x-filament::button>
            BLADE)));
        }

        $components = [$wizard];

        if ($stagingId !== null) {
            $components[] = Hidden::make('staging_id')->default($stagingId);
        }

        return $schema->components($components);
    }
}
