<?php

declare(strict_types=1);

namespace App\Filament\Resources\GestionEmpleados\Pages;

use App\Filament\Resources\GestionEmpleados\GestionEmpleadosResource;
use App\Models\FiltroColaborador;
use App\Models\User;
use App\Services\ColaboradoresFiltroAdminService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class FiltrarColaboradoresEmpresaUsuario extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = GestionEmpleadosResource::class;

    /** @var array<string, mixed> */
    public array $data = [];

    public int $usuarioId;

    private const MESES_NACIMIENTO = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];

    public function mount(int|string $record, int|string|User $usuario): void
    {
        Gate::authorize('viewAny', FiltroColaborador::class);

        $this->record = $this->resolveRecord($record);
        $this->usuarioId = $usuario instanceof User ? (int) $usuario->getKey() : (int) $usuario;

        $user = User::query()->findOrFail($this->usuarioId);
        abort_unless($user->perteneceAEmpresa((int) $this->getRecord()->getKey()), 404);

        $filtroGuardado = FiltroColaborador::query()
            ->where('empresa_id', $this->getRecord()->id)
            ->where('user_id', $this->usuarioId)
            ->latest('updated_at')
            ->first();

        $this->data = ColaboradoresFiltroAdminService::formularioDesdeFiltro($filtroGuardado);
    }

    protected function usuarioDestino(): User
    {
        return User::query()->findOrFail($this->usuarioId);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a usuarios')
                ->url(GestionEmpleadosResource::getUrl('usuarios', ['record' => $this->getRecord()])),
            Action::make('guardarFiltro')
                ->label('Guardar filtro')
                ->icon('heroicon-o-bookmark')
                ->visible(fn (): bool => Gate::allows('create', FiltroColaborador::class))
                ->form([
                    TextInput::make('nombre')
                        ->label('Nombre del filtro')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    Gate::authorize('create', FiltroColaborador::class);
                    ColaboradoresFiltroAdminService::persistir(
                        $data['nombre'],
                        $this->getRecord(),
                        $this->usuarioDestino(),
                        $this->data
                    );
                    Notification::make()
                        ->title('Filtro guardado correctamente')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->nombre.' — '.$this->usuarioDestino()->name;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Criterios de filtrado')
                    ->description('Filtra colaboradores de la empresa. Puedes guardar la configuración para reutilizarla.')
                    ->schema([
                        Select::make('region_id')
                            ->label('Región')
                            ->options(fn (): array => $this->getRecord()->regiones()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('ubicacion_id')
                            ->label('Ubicación')
                            ->options(fn (): array => $this->getRecord()->ubicaciones()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(fn (): array => $this->getRecord()->departamentos()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('area_id')
                            ->label('Área')
                            ->options(fn (): array => $this->getRecord()->areas()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('puesto_id')
                            ->label('Puesto')
                            ->options(fn (): array => $this->getRecord()->puestos()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('generos')
                            ->label('Género')
                            ->options([
                                'M' => 'Masculino',
                                'F' => 'Femenino',
                                'Otro' => 'Otro',
                            ])
                            ->multiple()
                            ->live(),
                        Select::make('meses')
                            ->label('Mes de nacimiento')
                            ->options(self::MESES_NACIMIENTO)
                            ->multiple()
                            ->live(),
                        Grid::make()
                            ->schema([
                                TextInput::make('edad_desde')
                                    ->label('Edad desde')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->live(),
                                TextInput::make('edad_hasta')
                                    ->label('Edad hasta')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->live(),
                                TextInput::make('mes_desde')
                                    ->label('Antigüedad (meses) desde')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(600)
                                    ->live(),
                                TextInput::make('mes_hasta')
                                    ->label('Antigüedad (meses) hasta')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(600)
                                    ->live(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form-filtro-colaboradores'),
                Section::make('Colaboradores que cumplen el filtro')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    protected function makeTable(): Table
    {
        return Table::make($this)
            ->query(fn (): Builder => $this->queryColaboradoresFiltrados());
    }

    /**
     * @return Builder<User>
     */
    protected function queryColaboradoresFiltrados(): Builder
    {
        $query = User::query()
            ->pertenecenAEmpresa((int) $this->getRecord()->getKey())
            ->with(['colaborador.region', 'colaborador.ubicacion', 'colaborador.departamento', 'colaborador.area', 'colaborador.puesto']);

        return ColaboradoresFiltroAdminService::aplicarFiltrosFormulario($query, $this->data ?? []);
    }

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('gestion_empleados_colaboradores')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('numero_colaborador')
                    ->label('Nº colaborador')
                    ->sortable(),
                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $like = "%{$search}%";
                            $q->where('users.name', 'like', $like)
                                ->orWhere('users.apellido_paterno', 'like', $like)
                                ->orWhere('users.apellido_materno', 'like', $like)
                                ->orWhereHas('colaborador', function (Builder $cq) use ($like): void {
                                    $cq->where('nombre', 'like', $like)
                                        ->orWhere('apellido_paterno', 'like', $like)
                                        ->orWhere('apellido_materno', 'like', $like);
                                });
                        });
                    }),
                TextColumn::make('colaborador.region.nombre')
                    ->label('Región'),
                TextColumn::make('colaborador.ubicacion.nombre')
                    ->label('Ubicación'),
                TextColumn::make('colaborador.departamento.nombre')
                    ->label('Departamento'),
                TextColumn::make('colaborador.area.nombre')
                    ->label('Área'),
                TextColumn::make('colaborador.puesto.nombre')
                    ->label('Puesto'),
                TextColumn::make('colaborador.genero')
                    ->label('Género'),
            ])
            ->emptyStateHeading('Ningún colaborador cumple el filtro');
    }
}
