<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\BajasColaboradores\Pages;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Models\Area;
use App\Models\BajaColaborador;
use App\Models\CentroPago;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Puesto;
use App\Models\Region;
use App\Models\Ubicacion;
use App\Services\ReingresoColaboradorService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReingresarColaborador extends Page
{
    protected static string $resource = BajaColaboradorResource::class;

    protected static ?string $title = 'Reingresar colaborador';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.cliente.resources.bajas-colaboradores.pages.reingresar-colaborador';

    public ?array $data = [];

    public BajaColaborador $baja;

    public function mount(int|string $record): void
    {
        abort_unless(auth()->user()?->can('Create:Colaborador'), 403);

        $this->baja = BajaColaboradorResource::getEloquentQuery()
            ->whereKey($record)
            ->firstOrFail();

        if (! $this->baja->puedeReingresar()) {
            Notification::make()
                ->title('No se puede reingresar')
                ->body('Este colaborador no puede ser reingresado.')
                ->danger()
                ->send();

            $this->redirect(BajaColaboradorResource::getUrl('index', [
                'tenant' => Filament::getTenant(),
            ]));

            return;
        }

        $datosPreLlenados = app(ReingresoColaboradorService::class)->obtenerDatosParaReingreso($this->baja);
        $this->form->fill($datosPreLlenados);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Reingresar — '.$this->baja->colaborador?->nombre_completo;
    }

    public function getCancelUrl(): string
    {
        return BajaColaboradorResource::getUrl('index', [
            'tenant' => Filament::getTenant(),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $empresaId = self::empresaId();

        return $schema
            ->components([
                Section::make('Datos personales')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('apellido_paterno')
                            ->label('Apellido paterno')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('apellido_materno')
                            ->label('Apellido materno')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telefono_movil')
                            ->label('Teléfono móvil')
                            ->tel()
                            ->maxLength(20),
                        DatePicker::make('fecha_nacimiento')
                            ->label('Fecha de nacimiento')
                            ->native(false),
                    ]),
                Section::make('Datos laborales')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('fecha_ingreso')
                            ->label('Nueva fecha de ingreso')
                            ->required()
                            ->native(false)
                            ->helperText('Debe ser posterior a la fecha de baja.'),
                        Select::make('ubicacion_id')
                            ->label('Ubicación')
                            ->options(fn (): array => $empresaId ? Ubicacion::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(fn (): array => $empresaId ? Departamento::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('area_id')
                            ->label('Área')
                            ->options(fn (): array => $empresaId ? Area::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('puesto_id')
                            ->label('Puesto')
                            ->options(fn (): array => $empresaId ? Puesto::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('region_id')
                            ->label('Región')
                            ->options(fn (): array => $empresaId ? Region::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('centro_pago_id')
                            ->label('Centro de pago')
                            ->options(fn (): array => $empresaId ? CentroPago::query()->where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all() : [])
                            ->searchable(),
                        Select::make('razon_social_id')
                            ->label('Razón social')
                            ->options(fn (): array => Filament::getTenant() instanceof Empresa
                                ? Filament::getTenant()
                                    ->razonesSociales()
                                    ->orderBy('razones_sociales.nombre')
                                    ->get()
                                    ->mapWithKeys(fn ($r): array => [$r->id => $r->nombre])
                                    ->all()
                                : [])
                            ->searchable(),
                        Select::make('periodicidad_pago')
                            ->label('Periodicidad de pago')
                            ->options([
                                'SEMANAL' => 'Semanal',
                                'CATORCENAL' => 'Catorcenal',
                                'QUINCENAL' => 'Quincenal',
                                'MENSUAL' => 'Mensual',
                            ])
                            ->required(),
                    ]),
                Section::make('Datos salariales')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('salario_bruto')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->prefix('$'),
                        TextInput::make('salario_neto')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->prefix('$'),
                        TextInput::make('monto_maximo')
                            ->label('Monto máximo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->prefix('$'),
                    ]),
                Section::make('Reingreso')
                    ->schema([
                        TextInput::make('motivo_reingreso')
                            ->label('Motivo del reingreso')
                            ->maxLength(255),
                        Textarea::make('comentarios')
                            ->rows(3),
                        Toggle::make('crear_usuario')
                            ->label('Crear usuario de acceso')
                            ->default(true)
                            ->helperText('El colaborador tendrá acceso al sistema con usuario y contraseña.'),
                        TextInput::make('password')
                            ->label('Contraseña del nuevo usuario')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->visible(fn (Get $get): bool => (bool) $get('crear_usuario'))
                            ->helperText('Opcional. Si no se indica, se generará una aleatoria. Mínimo 8 caracteres.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('reingresar')
                    ->footer([
                        Actions::make([
                            Action::make('reingresar')
                                ->label('Confirmar reingreso')
                                ->color('success')
                                ->submit('reingresar'),
                        ]),
                    ]),
            ]);
    }

    public function reingresar(): void
    {
        $data = $this->form->getState();

        try {
            $reingreso = app(ReingresoColaboradorService::class)->reingresar($this->baja, $data);
            $reingreso->loadMissing('colaboradorNuevo');

            Notification::make()
                ->title('Colaborador reingresado')
                ->body('Se creó un nuevo registro para '.$reingreso->colaboradorNuevo->nombre_completo.'.')
                ->success()
                ->send();

            $this->redirect(BajaColaboradorResource::getUrl('index', [
                'tenant' => Filament::getTenant(),
            ]));
        } catch (ValidationException $e) {
            Notification::make()
                ->title('No se pudo reingresar')
                ->body(collect($e->errors())->flatten()->first() ?? 'Revise los datos.')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Error al reingresar')
                ->body(config('app.debug') ? $e->getMessage() : 'Ocurrió un error. Intente de nuevo o contacte a soporte.')
                ->danger()
                ->send();
        }
    }

    private static function empresaId(): ?int
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Empresa ? $tenant->id : null;
    }
}
