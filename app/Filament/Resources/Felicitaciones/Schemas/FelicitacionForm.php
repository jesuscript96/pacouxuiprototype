<?php

namespace App\Filament\Resources\Felicitaciones\Schemas;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use App\Services\ArchivoService;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FelicitacionForm
{
    private const string MODULO = 'felicitaciones';

    /** @var list<array{campo: string, marcador: string}> */
    private const array MARCADORES = [
        ['campo' => 'nombre', 'marcador' => '[Nombre]'],
        ['campo' => 'apellido_paterno', 'marcador' => '[Apellido paterno]'],
        ['campo' => 'apellido_materno', 'marcador' => '[Apellido materno]'],
    ];

    /**
     * Regla de validación personalizada para el campo mensaje.
     *
     * Verifica que el mensaje incluya al menos uno de los marcadores
     * de destinatario: [Nombre], [Apellido paterno] o [Apellido materno].
     * El RichEditor puede entregar un array TipTap o un string HTML.
     *
     * @return \Closure(string, mixed, \Closure): void
     */
    public static function mensajeDestinatarioLaravelRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value)) {
                $content = $value;
            } elseif (is_array($value)) {
                $json = json_encode($value, JSON_UNESCAPED_UNICODE);
                $content = $json !== false ? $json : '';
            } else {
                $content = '';
            }

            foreach (self::MARCADORES as $item) {
                if (str_contains($content, $item['marcador'])) {
                    return;
                }
            }

            $fail('Incluye al menos Nombre, Apellido paterno o Apellido materno');
        };
    }

    /**
     * Genera el callback para afterStateUpdated de cada toggle individual
     * de marcador (nombre, apellido_paterno, apellido_materno).
     *
     * Al activar: agrega el marcador al final del mensaje.
     * Al desactivar: elimina todas las ocurrencias del marcador.
     */
    private static function callbackToggleMarcador(string $campo, string $marcador): Closure
    {
        return function (Get $get, Set $set) use ($campo, $marcador): void {
            $mensaje = $get('mensaje') ?? '';

            if ($get($campo)) {
                $set('mensaje', $mensaje.' '.$marcador);
            } else {
                $set('mensaje', str_replace($marcador, '', $mensaje));
            }
        };
    }

    /**
     * Callback para el toggle "Todos".
     *
     * Al activar: enciende los tres toggles individuales y agrega
     * todos los marcadores ([Empresa], [Nombre], [Apellido paterno], [Apellido materno]).
     * Al desactivar: apaga los tres toggles y elimina todos los marcadores.
     */
    private static function callbackToggleTodos(): Closure
    {
        return function (Get $get, Set $set): void {
            $activar = (bool) $get('todos');
            $mensaje = $get('mensaje') ?? '';

            foreach (self::MARCADORES as $item) {
                $set($item['campo'], $activar);
            }

            if ($activar) {
                $marcadoresTexto = '[Empresa] [Nombre] [Apellido paterno] [Apellido materno]';
                $set('mensaje', $mensaje.' '.$marcadoresTexto);
            } else {
                $mensaje = str_replace(['[Empresa]', '[Nombre]', '[Apellido paterno]', '[Apellido materno]'], '', $mensaje);
                $set('mensaje', $mensaje);
            }
        };
    }

    /**
     * Construye la sección "Empresa y remitente".
     *
     * Permite seleccionar la empresa (filtrada por tenant o permisos del usuario),
     * el departamento (filtrado por empresa seleccionada), y el remitente
     * (usuarios de la empresa seleccionada). Cambiar de empresa resetea
     * los campos dependientes.
     */
    private static function seccionEmpresaYRemitente(): Section
    {
        return Section::make('Empresa y remitente')
            ->schema([
                Select::make('empresa_id')
                    ->label('Empresa')
                    ->options(function (): Collection {
                        $tenant = Filament::getTenant();
                        if ($tenant instanceof Empresa) {
                            return collect([$tenant->id => $tenant->nombre]);
                        }

                        if (auth()->user()?->empresas->isNotEmpty()) {
                            return Empresa::query()
                                ->whereIn('id', auth()->user()->empresas->pluck('id'))
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id');
                        }

                        return Empresa::query()->orderBy('nombre')->pluck('nombre', 'id');
                    })
                    ->default(fn (): ?int => Filament::getTenant() instanceof Empresa
                        ? Filament::getTenant()->id
                        : null)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->inlineLabel()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('departamento_id', null);
                        $set('user_id', null);
                    }),

                Select::make('departamento_id')
                    ->label('Departamento')
                    ->inlineLabel()
                    ->options(fn (Get $get): Collection => Departamento::query()
                        ->where('empresa_id', $get('empresa_id'))
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('empresa_id'))),

                Select::make('user_id')
                    ->label('Remitente')
                    ->inlineLabel()
                    ->options(function (Get $get): Collection {
                        $empresaId = $get('empresa_id');
                        if (blank($empresaId)) {
                            return collect();
                        }

                        return User::query()
                            ->whereHas('empresas', fn ($q) => $q->where('empresas.id', $empresaId))
                            ->orderBy('name')
                            ->get()
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->disabled(fn (Get $get): bool => blank($get('empresa_id'))),
            ])
            ->columns(1)
            ->columnSpanFull();
    }

    /**
     * Construye la sección "Contenido".
     *
     * Incluye título, tipo (cumpleaños/aniversario), urgencia,
     * editor de mensaje con marcadores de destinatario, y campo
     * de logo que se almacena vía ArchivoService en Wasabi/S3.
     */
    private static function seccionContenido(): Section
    {
        $archivoService = app(ArchivoService::class);

        return Section::make('Contenido')
            ->schema([
                TextInput::make('titulo')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'CUMPLEAÑOS' => 'CUMPLEAÑOS',
                        'ANIVERSARIO' => 'ANIVERSARIO',
                    ])
                    ->live()
                    ->default('CUMPLEAÑOS')
                    ->required(),

                Select::make('es_urgente')
                    ->label('Es urgente')
                    ->options([
                        '1' => 'Sí',
                        '0' => 'No',
                    ])
                    ->default(1)
                    ->required()
                    ->live(),

                RichEditor::make('mensaje')
                    ->label('Mensaje')
                    ->required()
                    ->rule(static fn (): Closure => static::mensajeDestinatarioLaravelRule())
                    ->columnSpanFull()
                    ->live()
                    ->extraInputAttributes(['style' => 'min-height: 300px;'])
                    ->default('<p>[Nombre] [Apellido paterno] [Apellido materno] [Empresa]</p>')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        ['h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
                    ]),

                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->rules('file|mimes:jpg,jpeg,png|max:20000')
                    ->disk($archivoService->nombreDisco())
                    ->directory(fn (Get $get): string => 'companies/'.($get('empresa_id') ?? 'temp').'/'.self::MODULO)
                    ->fetchFileInformation(false)
                    ->dehydrated(true)
                    ->nullable()
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get): string {
                        $empresaId = $get('empresa_id') ?? 'unknown';

                        return $empresaId.'_'.time().'.'.$file->getClientOriginalExtension();
                    })
                    ->getUploadedFileUsing(function (mixed $component, string $file, mixed $storedFileNames) use ($archivoService): ?array {
                        // BL: No hacemos HEAD requests a S3 para evitar latencia.
                        // Confiamos en la BD como fuente de verdad de archivos existentes.
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
                    }),
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    /**
     * Construye la sección "Datos de destinatario".
     *
     * Controla qué marcadores de destinatario ([Nombre], [Apellido paterno],
     * [Apellido materno]) se incluyen en el mensaje. El toggle "Todos"
     * activa/desactiva los tres de golpe.
     */
    private static function seccionDestinatario(): Section
    {
        return Section::make('Datos de destinatario')
            ->schema([
                Toggle::make('todos')
                    ->label('Todos')
                    ->default(true)
                    ->live()
                    ->afterStateUpdated(static::callbackToggleTodos()),

                Toggle::make('nombre')
                    ->label('Nombre')
                    ->default(true)
                    ->live()
                    ->afterStateUpdated(static::callbackToggleMarcador('nombre', '[Nombre]')),

                Toggle::make('apellido_paterno')
                    ->label('Ap. Paterno')
                    ->default(true)
                    ->live()
                    ->afterStateUpdated(static::callbackToggleMarcador('apellido_paterno', '[Apellido paterno]')),

                Toggle::make('apellido_materno')
                    ->label('Ap. Materno')
                    ->default(true)
                    ->live()
                    ->afterStateUpdated(static::callbackToggleMarcador('apellido_materno', '[Apellido materno]')),
            ])
            ->columns(4)
            ->columnSpanFull();
    }

    /**
     * Configura el formulario completo de Felicitación.
     *
     * Layout en 2 columnas:
     * - Izquierda: empresa/remitente + contenido (título, tipo, mensaje, logo)
     * - Derecha: toggles de marcadores de destinatario + previsualización
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        static::seccionEmpresaYRemitente(),
                        static::seccionContenido(),
                    ]),

                Group::make()
                    ->schema([
                        static::seccionDestinatario(),
                        Section::make('Previsualización')
                            ->schema([
                                ViewField::make('preview')
                                    ->view('filament.forms.components.felicitacion-preview')
                                    ->dehydrated(false),
                            ]),
                    ]),
            ])->columns(2);
    }
}
