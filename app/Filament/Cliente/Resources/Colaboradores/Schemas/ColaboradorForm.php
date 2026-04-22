<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Schemas;

use App\Models\Area;
use App\Models\AreaGeneral;
use App\Models\Banco;
use App\Models\CentroPago;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Ocupacion;
use App\Models\Puesto;
use App\Models\PuestoGeneral;
use App\Models\Region;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ColaboradorForm
{
    private static function empresaId(): ?int
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Empresa ? $tenant->id : null;
    }

    /**
     * Select básico de catálogo filtrado por empresa (sin creación inline).
     * Usar para catálogos complejos como Ubicación o que no admiten creación rápida.
     */
    private static function catalogSelect(string $attribute, string $relationshipName, string $titleAttribute, string $label): Select
    {
        $empresaId = self::empresaId();

        return Select::make($attribute)
            ->label($label)
            ->relationship($relationshipName, $titleAttribute, fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable();
    }

    /**
     * Select de Departamento con opción de crear uno nuevo directamente desde el formulario.
     */
    private static function selectDepartamento(): Select
    {
        $empresaId = self::empresaId();

        return Select::make('departamento_id')
            ->label('Departamento')
            ->relationship('departamento', 'nombre', fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable()
            ->createOptionForm([
                TextInput::make('nombre')
                    ->label('Nombre del departamento')
                    ->required()
                    ->maxLength(255),
            ])
            ->createOptionUsing(fn (array $data): int => Departamento::create([
                'nombre' => $data['nombre'],
                'empresa_id' => self::empresaId(),
            ])->getKey());
    }

    /**
     * Select de Región con opción de crear una nueva directamente desde el formulario.
     */
    private static function selectRegion(): Select
    {
        $empresaId = self::empresaId();

        return Select::make('region_id')
            ->label('Región')
            ->relationship('region', 'nombre', fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable()
            ->createOptionForm([
                TextInput::make('nombre')
                    ->label('Nombre de la región')
                    ->required()
                    ->maxLength(255),
            ])
            ->createOptionUsing(fn (array $data): int => Region::create([
                'nombre' => $data['nombre'],
                'empresa_id' => self::empresaId(),
            ])->getKey());
    }

    /**
     * Select de Centro de pago con opción de crear uno nuevo directamente desde el formulario.
     */
    private static function selectCentroPago(): Select
    {
        $empresaId = self::empresaId();

        return Select::make('centro_pago_id')
            ->label('Centro de pago')
            ->relationship('centroPago', 'nombre', fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable()
            ->createOptionForm([
                TextInput::make('nombre')
                    ->label('Nombre del centro de pago')
                    ->required()
                    ->maxLength(255),
                TextInput::make('registro_patronal')
                    ->label('Registro patronal')
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('direccion_imss')
                    ->label('Dirección IMSS')
                    ->maxLength(255)
                    ->nullable(),
            ])
            ->createOptionUsing(fn (array $data): int => CentroPago::create([
                'nombre' => $data['nombre'],
                'registro_patronal' => $data['registro_patronal'] ?? null,
                'direccion_imss' => $data['direccion_imss'] ?? null,
                'empresa_id' => self::empresaId(),
            ])->getKey());
    }

    /**
     * Select de Área con opción de crear una nueva (incluyendo su Área general) desde el formulario.
     */
    private static function selectArea(): Select
    {
        $empresaId = self::empresaId();

        return Select::make('area_id')
            ->label('Área')
            ->relationship('area', 'nombre', fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable()
            ->createOptionForm([
                TextInput::make('nombre')
                    ->label('Nombre del área')
                    ->required()
                    ->maxLength(255),
                Select::make('area_general_id')
                    ->label('Área general')
                    ->options(fn (): array => AreaGeneral::query()
                        ->where('empresa_id', Filament::getTenant()?->id)
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre del área general')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(fn (array $data): int => AreaGeneral::create([
                        'nombre' => $data['nombre'],
                        'empresa_id' => Filament::getTenant()?->id,
                    ])->getKey()),
            ])
            ->createOptionUsing(fn (array $data): int => Area::create([
                'nombre' => $data['nombre'],
                'area_general_id' => $data['area_general_id'],
                'empresa_id' => self::empresaId(),
            ])->getKey());
    }

    /**
     * Select de Puesto con opción de crear uno nuevo (con puesto general, área general y ocupación) desde el formulario.
     */
    private static function selectPuesto(): Select
    {
        $empresaId = self::empresaId();

        return Select::make('puesto_id')
            ->label('Puesto')
            ->relationship('puesto', 'nombre', fn ($q) => $q && $empresaId ? $q->where('empresa_id', $empresaId) : $q)
            ->searchable()
            ->preload()
            ->nullable()
            ->createOptionForm([
                TextInput::make('nombre')
                    ->label('Nombre del puesto')
                    ->required()
                    ->maxLength(255),
                Select::make('puesto_general_id')
                    ->label('Puesto general')
                    ->options(fn (): array => PuestoGeneral::query()
                        ->where('empresa_id', Filament::getTenant()?->id)
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre del puesto general')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(fn (array $data): int => PuestoGeneral::create([
                        'nombre' => $data['nombre'],
                        'empresa_id' => Filament::getTenant()?->id,
                    ])->getKey()),
                Select::make('area_general_id')
                    ->label('Área general')
                    ->options(fn (): array => AreaGeneral::query()
                        ->where('empresa_id', Filament::getTenant()?->id)
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id')
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre del área general')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(fn (array $data): int => AreaGeneral::create([
                        'nombre' => $data['nombre'],
                        'empresa_id' => Filament::getTenant()?->id,
                    ])->getKey()),
                Select::make('ocupacion_id')
                    ->label('Ocupación')
                    ->options(fn (): array => Ocupacion::query()
                        ->orderBy('descripcion')
                        ->pluck('descripcion', 'id')
                        ->toArray())
                    ->searchable()
                    ->required(),
            ])
            ->createOptionUsing(fn (array $data): int => Puesto::create([
                'nombre' => $data['nombre'],
                'puesto_general_id' => $data['puesto_general_id'],
                'area_general_id' => $data['area_general_id'],
                'ocupacion_id' => $data['ocupacion_id'],
                'empresa_id' => self::empresaId(),
            ])->getKey());
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Datos personales')
                        ->description('Información personal del colaborador')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
                                    TextInput::make('apellido_paterno')->label('Apellido paterno')->required()->maxLength(255),
                                    TextInput::make('apellido_materno')->label('Apellido materno')->required()->maxLength(255),
                                    TextInput::make('email')->email()->nullable()->maxLength(255),
                                    TextInput::make('telefono_movil')->label('Teléfono móvil')->tel()->nullable()->maxLength(10),
                                    DatePicker::make('fecha_nacimiento')->required()->native(false)->maxDate(now()),
                                    Select::make('genero')
                                        ->label('Género')
                                        ->options(['M' => 'Masculino', 'F' => 'Femenino', 'OTRO' => 'Otro'])
                                        ->nullable(),
                                    TextInput::make('curp')
                                        ->label('CURP')
                                        ->hint('Clave Única de Registro de Población')
                                        ->maxLength(18)
                                        ->nullable(),
                                    TextInput::make('rfc')
                                        ->label('RFC')
                                        ->hint('Registro Federal de Contribuyentes')
                                        ->maxLength(13)
                                        ->nullable(),
                                    TextInput::make('nss')
                                        ->label('NSS')
                                        ->hint('Número de Seguridad Social')
                                        ->maxLength(11)
                                        ->nullable(),
                                    Select::make('estado_civil')
                                        ->options([
                                            'Soltero' => 'Soltero',
                                            'Casado' => 'Casado',
                                            'Divorciado' => 'Divorciado',
                                            'Viudo' => 'Viudo',
                                            'Unión libre' => 'Unión libre',
                                        ])
                                        ->nullable(),
                                    TextInput::make('nacionalidad')->maxLength(255)->nullable(),
                                    TextInput::make('direccion')->label('Dirección')->columnSpanFull()->nullable(),
                                ])
                                ->columns(2),
                        ]),
                    Step::make('Datos laborales')
                        ->description('Puesto, ubicación y horarios')
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextInput::make('numero_colaborador')->label('Número de colaborador')->nullable(),
                                    DatePicker::make('fecha_ingreso')->required()->native(false),
                                    self::catalogSelect('ubicacion_id', 'ubicacion', 'nombre', 'Ubicación'),
                                    self::selectDepartamento(),
                                    self::selectArea(),
                                    self::selectPuesto(),
                                    self::selectRegion(),
                                    self::selectCentroPago(),
                                    Select::make('razon_social_id')
                                        ->label('Razón social')
                                        ->options(fn () => Filament::getTenant() instanceof Empresa
                                            ? Filament::getTenant()
                                                ->razonesSociales()
                                                ->select('razones_sociales.id', 'razones_sociales.nombre')
                                                ->pluck('nombre', 'id')
                                                ->toArray()
                                            : [])
                                        ->searchable()
                                        ->nullable(),
                                    TimePicker::make('hora_entrada')->label('Hora entrada')->seconds(false)->nullable(),
                                    TimePicker::make('hora_salida')->label('Hora salida')->seconds(false)->nullable(),
                                    TimePicker::make('hora_entrada_comida')->label('Hora entrada comida')->seconds(false)->nullable(),
                                    TimePicker::make('hora_salida_comida')->label('Hora salida comida')->seconds(false)->nullable(),
                                    TimePicker::make('hora_entrada_extra')->label('Hora inicio horas extra')->seconds(false)->nullable(),
                                    TimePicker::make('hora_salida_extra')->label('Hora fin horas extra')->seconds(false)->nullable(),
                                    TextInput::make('comentario_adicional')->label('Comentario adicional')->columnSpanFull()->nullable(),
                                ])
                                ->columns(2),
                        ]),
                    Step::make('Nómina')
                        ->description('Salarios, periodicidad y cuenta bancaria')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Select::make('periodicidad_pago')
                                        ->label('Frecuencia de pago')
                                        ->options([
                                            'SEMANAL' => 'Semanal',
                                            'CATORCENAL' => 'Catorcenal',
                                            'QUINCENAL' => 'Quincenal',
                                            'MENSUAL' => 'Mensual',
                                        ])
                                        ->required(),
                                    TextInput::make('dia_periodicidad')->label('Día de corte')->numeric()->minValue(1)->maxValue(31)->nullable(),
                                    TextInput::make('salario_bruto')->numeric()->minValue(0)->maxValue(9999999999.99)->nullable(),
                                    TextInput::make('salario_neto')->numeric()->minValue(0)->maxValue(9999999999.99)->nullable(),
                                    TextInput::make('salario_diario')->numeric()->minValue(0)->maxValue(9999999999.99)->nullable(),
                                    TextInput::make('salario_diario_integrado')->hint('SDI — Base para el cálculo de prestaciones IMSS')->numeric()->minValue(0)->maxValue(9999999999.99)->nullable(),
                                    TextInput::make('monto_maximo')->label('Monto máximo')->numeric()->minValue(0)->maxValue(9999999999.99)->nullable(),
                                    TextInput::make('dias_vacaciones_anuales')->label('Días vacaciones legales')->numeric()->minValue(0)->maxValue(365)->default(0),
                                    TextInput::make('dias_vacaciones_restantes')->label('Días vacaciones empresa')->numeric()->minValue(0)->maxValue(365)->default(0),
                                    DatePicker::make('fecha_registro_imss')->label('Fecha registro IMSS')->hint('Fecha de alta ante el IMSS')->native(false)->nullable(),
                                    TextInput::make('nombre_empresa_pago')->label('Nombre empresa pago')->nullable(),
                                ])
                                ->columns(2),
                            Section::make('Cuenta nómina')
                                ->schema([
                                    Select::make('cuenta_nomina.banco_id')
                                        ->label('Banco')
                                        ->options(Banco::query()->orderBy('nombre')->pluck('nombre', 'id')->toArray())
                                        ->searchable()
                                        ->preload()
                                        ->nullable(),
                                    TextInput::make('cuenta_nomina.numero_cuenta')->label('Número de cuenta')->nullable(),
                                    Select::make('cuenta_nomina.tipo_cuenta')
                                        ->options(['CLABE' => 'CLABE interbancaria', 'TARJETA' => 'Tarjeta', 'CUENTA' => 'Cuenta'])
                                        ->nullable(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                        ]),
                    Step::make('Beneficiarios')
                        ->description('Beneficiarios y porcentajes (suma debe ser 100%)')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Repeater::make('beneficiarios')
                                        ->schema([
                                            TextInput::make('nombre_completo')->required(),
                                            TextInput::make('parentesco')->required(),
                                            TextInput::make('porcentaje')
                                                ->numeric()
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->required()
                                                ->suffix('%'),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->addActionLabel('Agregar beneficiario')
                                        ->helperText('La suma de los porcentajes debe ser 100%.'),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
