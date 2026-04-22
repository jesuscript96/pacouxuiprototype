<?php

namespace App\Filament\Resources\Empresas\Schemas;

use App\Models\CentroCosto;
use App\Models\Industria;
use App\Models\NotificacionesIncluidas;
use App\Models\Producto;
use App\Models\RazonEncuestaSalida;
use App\Models\Subindustria;
use App\Services\ArchivoService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmpresaForm
{
    /** Tamaño máximo en KB (Filepond + reglas Laravel; alinear con config livewire.temporary_file_upload). */
    private const int FOTO_MAX_KB = 5_100;

    /** Tamaño máximo en KB (logo en personalización). */
    private const int LOGO_MAX_KB = 5_100;

    public static function configure(Schema $schema): Schema
    {
        $archivoService = app(ArchivoService::class);
        $disco = $archivoService->nombreDisco();

        return $schema
            ->components([
                Section::make('Información de la empresa')
                    ->description('Datos generales de la empresa')
                    ->schema([
                        FileUpload::make('foto')
                            ->label('Foto')
                            ->image()
                            ->maxSize(self::FOTO_MAX_KB)
                            ->rules([
                                'nullable',
                                'file',
                                'mimes:jpg,jpeg,png,bmp',
                                'max:'.self::FOTO_MAX_KB,
                            ])
                            ->helperText('Imagen JPG, PNG o BMP. Tamaño máximo: '.round(self::FOTO_MAX_KB / 1024, 1).' MB.')
                            ->validationMessages([
                                'foto.max' => 'La foto no puede superar '.round(self::FOTO_MAX_KB / 1024, 1).' MB.',
                            ])
                            ->disk($disco)
                            ->visibility('public')
                            ->dehydrated(true)
                            ->getUploadedFileUsing(function (mixed $component, ?string $file, mixed $storedFileNames) use ($archivoService): ?array {
                                if (blank($file)) {
                                    return null;
                                }
                                if (! $archivoService->existe($file)) {
                                    return null;
                                }

                                $d = $archivoService->disco();

                                return [
                                    'name' => basename($file),
                                    'size' => $d->size($file),
                                    'type' => $d->mimeType($file),
                                    'url' => $archivoService->url($file),
                                ];
                            })
                            ->mutateDehydratedStateUsing(function (?string $state, ?Model $record): ?string {
                                if (blank($state)) {
                                    return null;
                                }

                                if ($record?->getKey() && $state === $record->foto) {
                                    return null;
                                }

                                return $state;
                            }),

                        TextInput::make('nombre')
                            ->label('Nombre de la empresa')
                            ->maxLength(200)
                            ->required(),
                        TextInput::make('nombre_contacto')
                            ->label('Nombre de contacto')
                            ->maxLength(200)
                            ->required(),
                        TextInput::make('email_contacto')
                            ->label('Email de contacto')
                            ->email()
                            ->maxLength(230)
                            ->required(),
                        TextInput::make('telefono_contacto')
                            ->label('Teléfono de oficina')
                            ->maxLength(10)
                            ->numeric()
                            ->required(),
                        TextInput::make('movil_contacto')
                            ->maxLength(10)
                            ->label('Celular')
                            ->numeric()
                            ->required(),
                        TextInput::make('email_facturacion')
                            ->label('Email de facturación')
                            ->email()
                            ->maxLength(230)
                            ->required(),

                        /********************* COMISIONES ************************************/
                        Select::make('tipo_comision')
                            ->label('Tipo de comisión')
                            ->options([
                                'PERCENTAGE' => 'Porcentaje',
                                'FIXED_AMOUNT' => 'Monto fijo',
                                'MIXED' => 'Mixto',
                            ])
                            ->default('PERCENTAGE')
                            ->afterStateUpdated(function (Set $set) {
                                $set('rango_comision_precio_desde', null);
                                $set('rango_comision_precio_hasta', null);
                                $set('rango_comision_monto_fijo', null);
                                $set('rango_comision_porcentaje', null);
                            })
                            ->required()
                            ->live(),
                        Group::make()
                            ->schema([
                                repeater::make('rango_comision')
                                    ->schema([
                                        TextInput::make('rango_comision_precio_desde')
                                            ->label('Precio desde')
                                            ->numeric()
                                            ->maxValue(9999999999.99)
                                            ->rules(['required_if:tipo_comision,MIXED', 'numeric', 'min:0']),

                                        TextInput::make('rango_comision_precio_hasta')
                                            ->label('Precio hasta')
                                            ->numeric()
                                            ->maxValue(9999999999.99)
                                            ->rules(function (Get $get): array {
                                                $rules = ['required_if:tipo_comision,MIXED', 'numeric', 'min:0'];
                                                $desde = $get('rango_comision_precio_desde');
                                                if (filled($desde) && is_numeric($desde)) {
                                                    $rules[] = function (string $attribute, $value, \Closure $fail) use ($desde): void {
                                                        if (filled($value) && is_numeric($value) && (float) $value <= (float) $desde) {
                                                            $fail(__('validation.gt.numeric', ['value' => $desde]));
                                                        }
                                                    };
                                                }

                                                return $rules;
                                            }),

                                        TextInput::make('rango_comision_monto_fijo')
                                            ->label('Monto fijo')
                                            ->numeric()
                                            ->maxValue(9999999999.99)
                                            ->rules(['required_if:tipo_comision,MIXED', 'numeric', 'min:0']),

                                        TextInput::make('rango_comision_porcentaje')
                                            ->label('Porcentaje %')
                                            ->numeric()
                                            ->rules(['required_if:tipo_comision,MIXED', 'numeric', 'min:0', 'max:100']),

                                    ])->columns(4)->columnSpanFull(),

                            ])
                            ->columns(4)
                            ->visible(fn (Get $get): bool => $get('tipo_comision') === 'MIXED'),

                        Group::make()
                            ->schema([
                                TextInput::make('comision_semanal')
                                    ->numeric()
                                    ->label('Comisión semanal')
                                    ->maxValue(1000)
                                    ->rules('required_unless:tipo_comision,MIXED|nullable|numeric|min:0'),
                                TextInput::make('comision_bisemanal')
                                    ->label('Comisión catorcenal')
                                    ->numeric()
                                    ->label('Comisión catorcenal')
                                    ->maxValue(1000)
                                    ->rules('required_unless:tipo_comision,MIXED|nullable|numeric|min:0'),
                                TextInput::make('comision_quincenal')
                                    ->numeric()
                                    ->label('Comisión quincenal')
                                    ->maxValue(1000)
                                    ->rules('required_unless:tipo_comision,MIXED|nullable|numeric|min:0'),
                                TextInput::make('comision_mensual')
                                    ->numeric()
                                    ->label('Comisión mensual')
                                    ->maxValue(1000)
                                    ->rules('required_unless:tipo_comision,MIXED|nullable|numeric|min:0'),
                                TextInput::make('comision_gateway')
                                    ->numeric()
                                    ->label('Comisión por pasarela de pago')
                                    ->maxValue(1000)
                                    ->rules('required_unless:tipo_comision,MIXED|nullable|numeric|min:0'),
                            ])
                            ->visible(fn (Get $get): bool => $get('tipo_comision') != 'MIXED'),

                        Select::make('industria_id')
                            ->label('Industria')
                            ->required()
                            ->options(Industria::cachedOptionsForSelect())
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('sub_industria_id', null);
                            })
                            // ->searchable()
                            ->preload(),
                        Select::make('sub_industria_id')
                            ->label('Sub industria')
                            ->options(fn (Get $get): Collection => Subindustria::query()
                                ->where('industria_id', $get('industria_id'))
                                ->pluck('nombre', 'id')
                            ),

                        Toggle::make('tiene_sub_empresas')
                            ->label('¿Los departamentos funcionan como subempresas?'),

                        DatePicker::make('fecha_inicio_contrato')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->date('Y-m-d')
                            ->required(),
                        DatePicker::make('fecha_fin_contrato')
                            ->minDate(fn (Get $get) => $get('fecha_inicio_contrato'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->date('Y-m-d')
                            ->required(),

                        TextInput::make('app_android_id')
                            ->label('ID de la app de Android')
                            ->maxLength(200)
                            ->required(),
                        TextInput::make('app_ios_id')
                            ->label('ID de la app de iOS')
                            ->maxLength(200)
                            ->required(),
                        // TextInput::make('app_huawei_id'),
                        TextInput::make('num_usuarios_reportes')
                            ->label('Número de usuarios para reportes')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999),
                        Toggle::make('tiene_analiticas_por_ubicacion')
                            ->label('Filtrar reportes por ubicación'),

                        Toggle::make('permitir_notificaciones_felicitaciones')
                            ->live()
                            ->label('Enviar automáticamente felicitaciones de cumpleaños y aniversario'),
                        Select::make('segmento_notificaciones_felicitaciones')
                            ->label('Segmentar notificaciones de aniversario y cumpleaños')
                            ->visible(fn (Get $get): bool => $get('permitir_notificaciones_felicitaciones') === true)
                            ->options([
                                'COMPANY' => 'Empresa',
                                'LOCATION' => 'Ubicación',
                            ]),

                        /************************************ RETENCIONES ************************************/
                        Toggle::make('permitir_retenciones')
                            ->live()
                            ->label('Activar retenciones de nómina (cuentas por cobrar)'),

                        Group::make()
                            ->schema([
                                Repeater::make('emails_retenciones')
                                    ->schema([
                                        // TODO: Revisar el campo de "correo para retenciones"
                                        TextInput::make('email_retencion')
                                            ->label('Correo para retenciones')
                                            ->email()
                                            ->maxLength(230),
                                    ]),
                                TextInput::make('dias_vencidos_retencion')
                                    ->label('Días vencidos para retención')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(999),
                                // TODO: Revisar este campo
                                DateTimePicker::make('dia_retencion_mensual')
                                    ->label('Día de retenciones mensuales')
                                    ->native(false),
                                // TODO: Revisar este campo
                                Select::make('dia_retencion_semanal')
                                    ->label('Día de retenciones semanales')
                                    ->options([
                                        '1' => 'Lunes',
                                        '2' => 'Martes',
                                        '3' => 'Miércoles',
                                        '4' => 'Jueves',
                                        '5' => 'Viernes',
                                        '6' => 'Sábado',
                                        '7' => 'Domingo',
                                    ]),
                                // TODO: Revisar este campo
                                Select::make('dia_retencion_catorcenal')
                                    ->label('Días antes de la catorcena para retenciones')
                                    ->options(
                                        array_combine(range(1, 14), range(1, 14))
                                    ),
                                // TODO: Revisar este campo
                                Select::make('dia_retencion_quincenal')
                                    ->label('Días antes de la quincena para retenciones')
                                    ->options(
                                        array_combine(range(1, 14), range(1, 14))
                                    ),
                            ])
                            ->visible(fn (Get $get): bool => $get('permitir_retenciones') === true),
                        /************************************ FIN RETENCIONES ************************************/

                        Toggle::make('tiene_pagos_catorcenales')
                            ->label('La empresa maneja pagos catorcenales')
                            ->live(),
                        DatePicker::make('fecha_proximo_pago_catorcenal')
                            ->label('Fecha del próximo pago catorcenal')
                            ->visible(fn (Get $get): bool => $get('tiene_pagos_catorcenales') === true)
                            ->minDate(fn (Get $get) => now()->addDays(1))
                            ->native(false),

                        // TODO: Revisar este campo -> no se guarda
                        Toggle::make('tiene_quincena_personalizada')
                            ->label('Establecer quincena personalizada. Permite establecer un día de inicio y fin para los pagos quincenales personalizados')
                            ->afterStateUpdated(function (Set $set) {
                                $set('dia_inicio', null);
                                $set('dia_fin', null);
                            })
                            ->live(),
                        Group::make()
                            ->schema([
                                Select::make('dia_inicio')
                                    ->label('Día de inicio')
                                    ->live()
                                    ->options(
                                        array_combine(range(1, 30), range(1, 30))
                                    )
                                    ->required(fn (Get $get): bool => $get('tiene_quincena_personalizada')),
                                Select::make('dia_fin')
                                    ->label('Día de finalización')
                                    ->options(
                                        array_combine(range(1, 30), range(1, 30))
                                    )
                                    ->required(fn (Get $get): bool => $get('tiene_quincena_personalizada'))
                                    ->rules([
                                        // Regla de validación personalizada
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $inicial = $get('dia_inicio');

                                            if ($get('tiene_quincena_personalizada') && filled($inicial) && filled($value)) {
                                                if ((int) $value <= (int) $inicial) {
                                                    $fail("El día de finalización debe ser mayor al día de inicio ({$inicial}).");
                                                }
                                            }
                                        },
                                    ]),
                            ])
                            ->columns(2)
                            ->visible(fn (Get $get): bool => $get('tiene_quincena_personalizada') === true),

                        Toggle::make('activo')
                            ->label('Empresa activa'),
                        // DateTimePicker::make('fecha_activacion'),
                        Toggle::make('tiene_limite_de_sesiones')
                            ->label('Cerrar sesión tras inactividad prolongada'),

                        Toggle::make('activar_finiquito')
                            ->label('Permitir agendar cita de finiquito')
                            ->live(),
                        TextInput::make('url_finiquito')
                            ->label('URL de la cita de finiquito')
                            ->url()
                            ->required(fn (Get $get): bool => $get('activar_finiquito') === true)
                            ->visible(fn (Get $get): bool => $get('activar_finiquito') === true),
                        Toggle::make('permitir_encuesta_salida')
                            ->label('Permitir encuesta de salida. Permite establecer si la empresa tiene encuesta de salida')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('razones', [])),

                        CheckboxList::make('razones')
                            ->options(RazonEncuestaSalida::opcionesCheckboxCatalogo())
                            ->visible(fn (Get $get) => $get('permitir_encuesta_salida'))
                            ->required(fn (Get $get) => $get('permitir_encuesta_salida')) // Validación nativa
                            ->minItems(1)
                            ->validationMessages([
                                'required' => 'La lista de razones no puede estar vacía.',
                                'min' => 'Por favor, marca al menos :min opción(es).',
                            ]),

                        Toggle::make('tiene_firma_nubarium')
                            ->label('Firma electrónica con Nubarium'),

                        // TODO: Revisar este campo - no se guarda
                        Toggle::make('aplicacion_compilada')
                            ->live()
                            ->label('App personalizada (white label)'),
                        Group::make()
                            ->schema([
                                TextInput::make('nombre_app')
                                    ->label('Nombre de la aplicación'),
                                TextInput::make('link_descarga_app')
                                    ->label('Link de descarga de la aplicación')
                                    ->url(),
                            ])
                            ->visible(fn (Get $get): bool => $get('aplicacion_compilada') === true),

                        Toggle::make('transacciones_con_imss')
                            ->label('Verificar historial IMSS para autorizar transacciones'),

                        TextInput::make('frecuencia_notificaciones_estado_animo')
                            ->label('Frecuencia de envíos para notificaciones de estado de ánimo (días)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365),

                        TextInput::make('vigencia_mensajes_urgentes')
                            ->label('Días de vigencia para mensajes urgentes')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999),
                        Toggle::make('validar_cuentas_automaticamente')
                            ->label('Verificación automática de cuentas bancarias'),
                        Toggle::make('enviar_boletin')
                            ->label('Enviar boletín informativo'),
                        Toggle::make('domiciliación_via_api')
                            ->label('Domiciliación bancaria (cobro automático con Belvo)'),
                        Toggle::make('descargar_cursos')
                            ->label('Descargar cursos. Permite descargar los cursos para poder visualizarlos en modo offline'),
                        FileUpload::make('documentos_contratos')
                            ->label('Documentos/Contratos')
                            ->acceptedFileTypes(['application/pdf'])
                            ->multiple()
                            ->disk($disco)
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->dehydrated(true)
                            ->getUploadedFileUsing(function (mixed $component, ?string $file, mixed $storedFileNames) use ($archivoService): ?array {
                                if (blank($file)) {
                                    return null;
                                }
                                if (! $archivoService->existe($file)) {
                                    return null;
                                }

                                $d = $archivoService->disco();

                                return [
                                    'name' => basename($file),
                                    'size' => $d->size($file),
                                    'type' => $d->mimeType($file) ?: 'application/pdf',
                                    'url' => $archivoService->url($file),
                                ];
                            }),

                    ]),

                Group::make()
                    ->schema([

                        Section::make('Razón social')
                            ->description('Datos de la razón social')
                            ->schema([
                                Repeater::make('razones_sociales')
                                    ->label('Razones sociales')
                                    ->schema([
                                        // Hidden::make('id'),
                                        TextInput::make('nombre')
                                            ->label('Nombre de la razón social')
                                            ->maxLength(200)
                                            ->required(),
                                        TextInput::make('rfc')
                                            ->label('RFC')
                                            ->maxLength(12)
                                            ->minLength(12)
                                            ->live()
                                            ->required(),
                                        TextInput::make('cp')
                                            ->numeric()
                                            ->maxLength(5)
                                            ->minLength(5)
                                            ->required()
                                            ->label('Código postal')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if (blank($state)) {
                                                    return;
                                                }

                                                $response = null;
                                                // Llamada a la API externa
                                                if (preg_match("/^(?:0?[1-9]|[1-9]\d|5[0-2])\d{3}$/", $state)) {

                                                    $URL = config('app.sepomex').'/'.$state;
                                                    $request = file_get_contents($URL);
                                                    $response = json_decode($request);
                                                }

                                                if ($response) {
                                                    // dd($response);
                                                    $colonias = [];
                                                    if (empty($response->estados[0])) {
                                                        Notification::make()
                                                            ->title('El código postal no es válido')
                                                            ->danger()
                                                            ->send();
                                                        $set('cp', null);

                                                        return;
                                                    }
                                                    foreach ($response->asentamientos ?? [] as $colonia) {
                                                        $colonias[$colonia] = $colonia;
                                                    }

                                                    // Seteamos los valores en otros campos del formulario
                                                    $set('alcaldia', $response->municipios[0] ?? '');
                                                    $set('estado', $response->estados[0] ?? '');
                                                    $set('api_options_storage', json_encode($colonias));
                                                }
                                            }),
                                        // Este campo sirve como puente de datos
                                        Hidden::make('api_options_storage')
                                            ->reactive(),
                                        // TextInput::make('registro_patronal')
                                        //     ->label('Registro patronal'),
                                        TextInput::make('calle')
                                            ->label('Calle')
                                            ->maxLength(100)
                                            ->required(),
                                        TextInput::make('numero_exterior')
                                            ->label('Número exterior')
                                            ->maxLength(6)
                                            ->required(),
                                        TextInput::make('numero_interior')
                                            ->label('Número interior')
                                            ->maxLength(6),
                                        Select::make('colonia')
                                            ->required()
                                            ->options(function (Get $get) {
                                                // El Select simplemente lee el JSON del campo oculto
                                                $storage = $get('api_options_storage');

                                                if (blank($storage)) {
                                                    return [];
                                                }

                                                return json_decode($storage, true);
                                            })
                                            ->live(),
                                        TextInput::make('alcaldia')
                                            ->required()
                                            ->maxLength(150),
                                        TextInput::make('estado')
                                            ->required()
                                            ->maxLength(100),

                                    ])
                                    ->columns(1)
                                    ->defaultItems(1)
                                    ->addActionLabel('Agregar razón social')
                                    ->collapsible()
                                    ->reorderable(false),
                            ]),

                        Section::make('Personalización')
                            ->description('Colores y logo de la app')
                            ->schema([
                                ColorPicker::make('color_primario'),
                                ColorPicker::make('color_secundario'),
                                ColorPicker::make('color_terciario'),
                                ColorPicker::make('color_cuarto'),
                                FileUpload::make('logo_url')
                                    ->label('Logo de la empresa')
                                    ->disk($disco)
                                    ->image()
                                    ->maxSize(self::LOGO_MAX_KB)
                                    ->rules([
                                        'nullable',
                                        'file',
                                        'mimes:jpg,jpeg,png,bmp',
                                        'max:'.self::LOGO_MAX_KB,
                                    ])
                                    ->helperText('Imagen JPG, PNG o BMP. Tamaño máximo: '.round(self::LOGO_MAX_KB / 1024, 1).' MB.')
                                    ->validationMessages([
                                        'logo_url.max' => 'El logo no puede superar '.round(self::LOGO_MAX_KB / 1024, 1).' MB.',
                                    ])
                                    ->visibility('public')
                                    ->dehydrated(true)
                                    ->getUploadedFileUsing(function (mixed $component, ?string $file, mixed $storedFileNames) use ($archivoService): ?array {
                                        if (blank($file)) {
                                            return null;
                                        }
                                        if (! $archivoService->existe($file)) {
                                            return null;
                                        }

                                        $d = $archivoService->disco();

                                        return [
                                            'name' => basename($file),
                                            'size' => $d->size($file),
                                            'type' => $d->mimeType($file),
                                            'url' => $archivoService->url($file),
                                        ];
                                    }),
                            ]),

                        Section::make('Centro de Costos')
                            ->description('Centros de costo por servicio')
                            ->schema([
                                Select::make('centro_costo_belvo_id')
                                    ->label('BELVO')
                                    ->options(CentroCosto::cachedOptionsForSelectByServicio('BELVO')),
                                Select::make('centro_costo_emida_id')
                                    ->label('EMIDA')
                                    ->options(CentroCosto::cachedOptionsForSelectByServicio('EMIDA')),
                                Select::make('centro_costo_stp_id')
                                    ->label('STP')
                                    ->options(CentroCosto::cachedOptionsForSelectByServicio('STP')),
                            ]),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Productos')
                            ->description('Asigna los productos que puede ofrecer esta empresa')
                            ->schema([
                                Repeater::make('productos')
                                    ->label('Productos')
                                    ->schema([
                                        // Hidden::make('id'),
                                        Select::make('producto_id')
                                            ->options(Producto::cachedOptionsForSelect())
                                            // ->relationship('productos', 'nombre')
                                            ->preload()
                                            ->required(),
                                        Select::make('desde')
                                            ->label('Habilitado desde (meses)')
                                            ->required()
                                            ->options(
                                                [
                                                    '1' => '1 mes',
                                                    '2' => '2 meses',
                                                    '3' => '3 meses',
                                                    '4' => '4 meses',
                                                    '5' => '5 meses',
                                                    '6' => '6 meses',
                                                    '7' => '7 meses',
                                                    '8' => '8 meses',
                                                    '9' => '9 meses',
                                                    '10' => '10 meses',
                                                    '11' => '11 meses',
                                                    '12' => '12 meses',
                                                ]
                                            ),
                                    ])
                                    ->columns(1)
                                    ->defaultItems(1)
                                    ->addActionLabel('Agregar producto')
                                    ->reorderable(false),
                            ]),

                        Section::make('Alias de transacciones')
                            ->description('Personaliza un alias para cada tipo de transacción')
                            ->schema([
                                TextInput::make('alias_transaccion_nomina')
                                    ->label('ADELANTO DE NÓMINA')
                                    ->maxLength(200),
                                TextInput::make('alias_transaccion_servicio')
                                    ->label('PAGO DE SERVICIO')
                                    ->maxLength(200),
                                TextInput::make('alias_transaccion_recarga')
                                    ->label('RECARGA')
                                    ->maxLength(200),

                            ]),

                        Section::make('Notificaciones')
                            ->description('Notificaciones habilitadas para empleados')
                            ->schema(
                                NotificacionesIncluidas::cachedAll()->map(function (NotificacionesIncluidas $notificacion) {
                                    return Toggle::make('notificaciones_incluidas.'.$notificacion->id)
                                        ->label($notificacion->nombre)
                                        ->default(true);
                                })->toArray()
                            ),
                    ]),

            ])
            ->columns(3);
    }
}
