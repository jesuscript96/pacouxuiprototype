<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Schemas;

use App\Models\Area;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\Puesto;
use App\Models\Ubicacion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class NotificacionPushForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Empresa')
                            ->description('Selecciona la empresa para la notificación')
                            ->schema([
                                Select::make('empresa_id')
                                    ->label('Empresa')
                                    ->options(fn (): array => Empresa::query()
                                        ->whereNull('deleted_at')
                                        ->orderBy('nombre')
                                        ->pluck('nombre', 'id')
                                        ->all())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('filtros.ubicaciones', []);
                                        $set('filtros.departamentos', []);
                                        $set('filtros.areas', []);
                                        $set('filtros.puestos', []);
                                        $set('filtros.destinatarios', [
                                            'select_all' => true,
                                            'manual_activation' => [],
                                            'manual_deactivation' => [],
                                        ]);
                                    })
                                    ->disabled(fn (?NotificacionPush $record): bool => $record?->exists === true && ! $record->esEditable()),
                            ])
                            ->columns(1),

                        Section::make('Contenido de la notificación')
                            ->description('Define el título y mensaje que verán los usuarios')
                            ->schema([
                                TextInput::make('titulo')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Nuevo comunicado importante')
                                    ->helperText('Máximo 255 caracteres. Será visible en la barra de notificaciones.'),
                                Textarea::make('mensaje')
                                    ->label('Mensaje')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(1000)
                                    ->placeholder('Escribe el contenido de la notificación...')
                                    ->helperText('Máximo 1000 caracteres.')
                                    ->columnSpanFull(),
                                TextInput::make('url')
                                    ->label('URL de redirección (opcional)')
                                    ->url()
                                    ->maxLength(500)
                                    ->placeholder('https://ejemplo.com/pagina')
                                    ->helperText('Si se proporciona, al tocar la notificación el usuario será redirigido a esta URL.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),

                        Section::make('Programación')
                            ->description('Define cuándo se enviará la notificación')
                            ->schema([
                                Radio::make('tipo_envio')
                                    ->label('Tipo de envío')
                                    ->options([
                                        'inmediato' => 'Enviar inmediatamente al guardar',
                                        'programado' => 'Programar para una fecha y hora específica',
                                        'borrador' => 'Guardar como borrador (enviar manualmente después)',
                                    ])
                                    ->default('borrador')
                                    ->required()
                                    ->live(),
                                DateTimePicker::make('programada_para')
                                    ->label('Fecha y hora de envío')
                                    ->required(fn (Get $get): bool => $get('tipo_envio') === 'programado')
                                    ->minDate(now())
                                    ->visible(fn (Get $get): bool => $get('tipo_envio') === 'programado')
                                    ->helperText('La notificación se enviará automáticamente en esta fecha y hora.'),
                            ])
                            ->collapsible(),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Destinatarios')
                            ->description('Filtra a quiénes se enviará la notificación. Si no seleccionas ningún filtro, se enviará a todos los colaboradores.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('filtros.ubicaciones')
                                            ->label('Ubicaciones')
                                            ->multiple()
                                            ->options(fn (Get $get): array => self::ubicacionesOptions($get))
                                            ->placeholder('Todas las ubicaciones')
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                        Select::make('filtros.areas')
                                            ->label('Áreas')
                                            ->multiple()
                                            ->options(fn (Get $get): array => self::areasOptions($get))
                                            ->placeholder('Todas las áreas')
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                        Select::make('filtros.departamentos')
                                            ->label('Departamentos')
                                            ->multiple()
                                            ->options(fn (Get $get): array => self::departamentosOptions($get))
                                            ->placeholder('Todos los departamentos')
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                        Select::make('filtros.puestos')
                                            ->label('Puestos')
                                            ->multiple()
                                            ->options(fn (Get $get): array => self::puestosOptions($get))
                                            ->placeholder('Todos los puestos')
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        Select::make('filtros.generos')
                                            ->label('Géneros')
                                            ->multiple()
                                            ->options([
                                                'M' => 'Masculino',
                                                'F' => 'Femenino',
                                                'O' => 'Otro',
                                            ])
                                            ->placeholder('Todos')
                                            ->live(),
                                        TextInput::make('filtros.edad_minima')
                                            ->label('Edad mínima')
                                            ->numeric()
                                            ->minValue(18)
                                            ->maxValue(100)
                                            ->placeholder('Sin mínimo')
                                            ->live(),
                                        TextInput::make('filtros.edad_maxima')
                                            ->label('Edad máxima')
                                            ->numeric()
                                            ->minValue(18)
                                            ->maxValue(100)
                                            ->placeholder('Sin máximo')
                                            ->live(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('filtros.antiguedad_minima_meses')
                                            ->label('Antigüedad mínima (meses)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(600)
                                            ->placeholder('Sin mínimo')
                                            ->live(),
                                        TextInput::make('filtros.antiguedad_maxima_meses')
                                            ->label('Antigüedad máxima (meses)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(600)
                                            ->placeholder('Sin máximo')
                                            ->live(),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('filtros.con_adeudos')
                                            ->label('Adeudos')
                                            ->options([
                                                '1' => 'Solo con adeudos',
                                                '0' => 'Solo sin adeudos',
                                            ])
                                            ->placeholder('Todos')
                                            ->live(),
                                        Select::make('filtros.cumpleaneros_mes')
                                            ->label('Cumpleañeros del mes')
                                            ->options([
                                                1 => 'Enero',
                                                2 => 'Febrero',
                                                3 => 'Marzo',
                                                4 => 'Abril',
                                                5 => 'Mayo',
                                                6 => 'Junio',
                                                7 => 'Julio',
                                                8 => 'Agosto',
                                                9 => 'Septiembre',
                                                10 => 'Octubre',
                                                11 => 'Noviembre',
                                                12 => 'Diciembre',
                                            ])
                                            ->placeholder('No filtrar por cumpleaños')
                                            ->live(),
                                    ]),
                                ViewField::make('lista_destinatarios')
                                    ->view('filament.admin.forms.components.notificacion-push-lista-destinatarios')
                                    ->viewData(function (Get $get): array {
                                        $filtros = $get('filtros') ?? [];
                                        $destinatariosEstado = $filtros['destinatarios'] ?? null;
                                        $segmentacion = $filtros;
                                        unset($segmentacion['destinatarios']);

                                        return [
                                            'empresaId' => $get('empresa_id'),
                                            'filtros' => $segmentacion,
                                            'destinatariosEstado' => is_array($destinatariosEstado) ? $destinatariosEstado : null,
                                        ];
                                    })
                                    ->dehydrated(false)
                                    ->visible(fn (Get $get): bool => filled($get('empresa_id')))
                                    ->columnSpanFull()
                                    ->extraFieldWrapperAttributes([
                                        'class' => 'w-full min-w-0 max-w-full [&_.fi-fo-field-content-col]:w-full [&_.fi-fo-field-content-col]:min-w-0',
                                        'style' => 'width: 100%; max-width: 100%;',
                                    ]),
                            ])
                            ->collapsible(),
                    ]),
            ])
            ->columns(2);
    }

    /**
     * @return array<int|string, string>
     */
    protected static function ubicacionesOptions(Get $get): array
    {
        $empresaId = $get('empresa_id');
        if (! filled($empresaId)) {
            return [];
        }

        return Ubicacion::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected static function areasOptions(Get $get): array
    {
        $empresaId = $get('empresa_id');
        if (! filled($empresaId)) {
            return [];
        }

        return Area::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected static function departamentosOptions(Get $get): array
    {
        $empresaId = $get('empresa_id');
        if (! filled($empresaId)) {
            return [];
        }

        return Departamento::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    protected static function puestosOptions(Get $get): array
    {
        $empresaId = $get('empresa_id');
        if (! filled($empresaId)) {
            return [];
        }

        return Puesto::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }
}
