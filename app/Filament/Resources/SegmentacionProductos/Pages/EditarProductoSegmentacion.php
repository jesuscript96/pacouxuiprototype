<?php

namespace App\Filament\Resources\SegmentacionProductos\Pages;

use App\Filament\Resources\SegmentacionProductos\SegmentacionProductosResource;
use App\Models\ColaboradorProducto;
use App\Models\FiltroProducto;
use App\Models\Producto;
use App\Models\User;
use App\Services\ColaboradoresFiltroAdminService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditarProductoSegmentacion extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = SegmentacionProductosResource::class;

    /** @var array<string, mixed> Estado del formulario de filtros (statePath 'data'). */
    public array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a empresas')
                ->url(SegmentacionProductosResource::getUrl('index')),
        ];
    }

    public function mount(int|string|null $record = null, int|string|Producto|null $producto = null): void
    {
        $record = $record ?? request()->route('record') ?? $this->getRecordIdFromUrl();
        $productoParam = $this->normalizeProductoParam($producto);

        if ($record === null || $productoParam === null) {
            throw new NotFoundHttpException('Parámetros de ruta inválidos (empresa y producto requeridos).');
        }

        try {
            $this->record = $this->resolveRecord($record);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(
                "No se encontró la empresa con ID {$record}. Comprueba que exista y no esté eliminada."
            );
        }

        try {
            $this->resolveProducto($productoParam);
        } catch (ModelNotFoundException $e) {
            $id = $productoParam instanceof Producto ? $productoParam->getKey() : $productoParam;
            throw new NotFoundHttpException(
                "No se encontró el producto con ID {$id}. Comprueba que exista y no esté eliminado."
            );
        } catch (HttpException $e) {
            throw $e;
        }

        $this->authorizeAccess();
        $this->data = $this->getFiltroData();
    }

    /**
     * Acepta producto desde la ruta (modelo), argumento (ID o JSON serializado) o segmentos de la URL.
     */
    protected function normalizeProductoParam(mixed $producto): int|string|Producto|null
    {
        $fromRoute = request()->route('producto');
        if ($fromRoute instanceof Producto) {
            return $fromRoute;
        }
        if ($producto instanceof Producto) {
            return $producto;
        }
        if (is_string($producto) && str_starts_with(trim($producto), '{')) {
            $decoded = json_decode($producto, true);
            if (isset($decoded['id'])) {
                return (string) $decoded['id'];
            }
        }
        if (is_numeric($producto) || (is_string($producto) && $producto !== '')) {
            return $producto;
        }

        return request()->route('producto') ?? $this->getProductoIdFromUrl();
    }

    protected function authorizeAccess(): void
    {
        //
    }

    /**
     * Fallback: extrae el ID de empresa desde la URL (segmentos .../1/producto/6/editar).
     */
    protected function getRecordIdFromUrl(): ?string
    {
        $path = request()->path();
        $segments = explode('/', $path);
        $slug = 'segmentacion-productos';
        $idx = array_search($slug, $segments, true);
        if ($idx !== false && isset($segments[$idx + 1]) && is_numeric($segments[$idx + 1])) {
            return $segments[$idx + 1];
        }

        return null;
    }

    /**
     * Fallback: extrae el ID de producto desde la URL (segmentos .../1/producto/6/editar).
     */
    protected function getProductoIdFromUrl(): ?string
    {
        $path = request()->path();
        $segments = explode('/', $path);
        $idx = array_search('producto', $segments, true);
        if ($idx !== false && isset($segments[$idx + 1]) && is_numeric($segments[$idx + 1])) {
            return $segments[$idx + 1];
        }

        return null;
    }

    protected function resolveProducto(int|string|Producto $producto): void
    {
        $productoModel = $producto instanceof Producto
            ? $producto
            : Producto::query()->findOrFail($producto);

        $empresa = $this->getRecord();
        if (! $empresa->productos()->where('productos.id', $productoModel->getKey())->exists()) {
            abort(404, 'El producto no pertenece a esta empresa.');
        }

        $this->producto = $productoModel;
    }

    public ?Producto $producto = null;

    /**
     * Convierte valor de filtro (array o escalar) a array para crossJoin; vacío => [null].
     *
     * @param  array<int|string>|int|string|null  $value
     * @return array<int|string|null>
     */
    protected function normalizeFilterIds(array|int|string|null $value): array
    {
        $arr = is_array($value) ? array_values($value) : (($value !== null && $value !== '') ? [$value] : []);

        return $arr === [] ? [null] : $arr;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFiltroData(): array
    {
        $filters = FiltroProducto::query()
            ->where('empresa_id', $this->getRecord()->getKey())
            ->where('producto_id', $this->producto->getKey())
            ->get();

        $first = $filters->first();
        $generos = [];
        $meses = [];
        if ($first?->generos) {
            $generos = is_string($first->generos) ? array_filter(explode(',', $first->generos)) : (array) $first->generos;
            $generos = $this->normalizarGenerosFiltroParaFicha($generos);
        }
        if ($first?->meses) {
            $meses = is_string($first->meses) ? array_filter(explode(',', $first->meses)) : (array) $first->meses;
        }

        $ubicacionIds = $filters->pluck('ubicacion_id')->filter()->unique()->values()->all();
        $departamentoIds = $filters->pluck('departamento_id')->filter()->unique()->values()->all();
        $areaIds = $filters->pluck('area_id')->filter()->unique()->values()->all();
        $puestoIds = $filters->pluck('puesto_id')->filter()->unique()->values()->all();
        $regionIds = $filters->pluck('region_id')->filter()->unique()->values()->all();

        return [
            'ubicacion_id' => count($ubicacionIds) > 0 ? $ubicacionIds : [],
            'departamento_id' => count($departamentoIds) > 0 ? $departamentoIds : [],
            'area_id' => count($areaIds) > 0 ? $areaIds : [],
            'puesto_id' => count($puestoIds) > 0 ? $puestoIds : [],
            'region_id' => count($regionIds) > 0 ? $regionIds : [],
            'edad_desde' => $first?->edad_desde ?? 0,
            'edad_hasta' => $first?->edad_hasta ?? 0,
            'mes_desde' => $first?->mes_desde ?? 0,
            'mes_hasta' => $first?->mes_hasta ?? 0,
            'generos' => $generos,
            'meses' => $meses,
            'razon' => $first?->razon ?? 'SERVICIO NO DISPONIBLE POR EL MOMENTO',
            'save_filters' => $filters->isNotEmpty(),
        ];
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filtros y opciones')
                    ->schema([
                        Select::make('ubicacion_id')
                            ->label('Ubicación')
                            ->options(fn () => optional($this->getRecord())->ubicaciones()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [])
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(fn () => optional($this->getRecord())->departamentos()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [])
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('area_id')
                            ->label('Área')
                            ->options(fn () => optional($this->getRecord())->areas()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [])
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->live(),
                        Select::make('puesto_id')
                            ->label('Puesto')
                            ->options(fn () => optional($this->getRecord())->puestos()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [])
                            ->multiple()
                            ->searchable()
                            ->live(),
                        Select::make('region_id')
                            ->label('Región')
                            ->options(fn () => optional($this->getRecord())->regiones()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [])
                            ->multiple()
                            ->searchable()
                            ->live(),
                        TextInput::make('edad_desde')
                            ->label('Edad desde')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $hasta = (int) $get('edad_hasta');
                                    if ((int) $value > 0 && $hasta > 0 && (int) $value > $hasta) {
                                        $fail('La edad desde no puede ser mayor que la edad hasta.');
                                    }
                                },
                            ]),
                        TextInput::make('edad_hasta')
                            ->label('Edad hasta')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $desde = (int) $get('edad_desde');
                                    if ((int) $value > 0 && $desde > 0 && (int) $value < $desde) {
                                        $fail('La edad hasta no puede ser menor que la edad desde.');
                                    }
                                },
                            ]),
                        TextInput::make('mes_desde')
                            ->label('Tiempo en empresa desde (meses)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(600)
                            ->default(0)
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $hasta = (int) $get('mes_hasta');
                                    if ((int) $value > 0 && $hasta > 0 && (int) $value > $hasta) {
                                        $fail('El mes desde no puede ser mayor que el mes hasta.');
                                    }
                                },
                            ]),
                        TextInput::make('mes_hasta')
                            ->label('Tiempo en empresa hasta (meses)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(600)
                            ->default(0)
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $desde = (int) $get('mes_desde');
                                    if ((int) $value > 0 && $desde > 0 && (int) $value < $desde) {
                                        $fail('El mes hasta no puede ser menor que el mes desde.');
                                    }
                                },
                            ]),
                        Select::make('generos')
                            ->label('Género')
                            ->options([
                                'M' => 'Masculino',
                                'F' => 'Femenino',
                                'OTRO' => 'Otro',
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
                                Select::make('razon')
                                    ->label('Razón (Desactivación)')
                                    ->options([
                                        'SERVICIO NO DISPONIBLE POR EL MOMENTO' => 'Producto temporalmente no disponible',
                                    ])
                                    ->required(),
                                Toggle::make('save_filters')
                                    ->label('Guardar configuración')
                                    ->helperText('Aplicar filtros automáticamente a futuros colaboradores'),
                                Actions::make([
                                    Action::make('guardar')
                                        ->label('Asignar producto a colaboradores')
                                        ->submit('save')
                                        ->keyBindings(['mod+s']),
                                ]),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->nombre.' -  Producto: '.$this->producto->nombre;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save'),
                Section::make('Colaboradores que cumplen el filtro')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    protected function makeTable(): Table
    {
        return Table::make($this)
            ->query(fn () => $this->getColaboradoresQuery());
    }

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('segmentacion_colaboradores')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('numero_colaborador')->label('Nº colaborador')->sortable(),
                TextColumn::make('nombre_completo')
                    ->label('Nombre completo')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $like = "%{$search}%";

                        return $query->where(function (Builder $q) use ($like): void {
                            $q->where('users.name', 'like', $like)
                                ->orWhere('users.apellido_paterno', 'like', $like)
                                ->orWhere('users.apellido_materno', 'like', $like);
                        });
                    }),
                TextColumn::make('colaborador.region.nombre')->label('Región'),
                TextColumn::make('colaborador.ubicacion.nombre')->label('Ubicación'),
                TextColumn::make('colaborador.departamento.nombre')->label('Departamento'),
                TextColumn::make('colaborador.area.nombre')->label('Área'),
                TextColumn::make('colaborador.puesto.nombre')->label('Puesto'),
            ])
            ->emptyStateHeading('Ningún colaborador cumple el filtro');
    }

    protected function getColaboradoresQuery(): Builder
    {
        $empresa = $this->getRecord();
        $data = $this->data ?? [];

        if (isset($data['generos']) && is_array($data['generos'])) {
            $data['generos'] = $this->expandirGeneroOtroParaConsulta(
                $this->normalizarGenerosFiltroParaFicha($data['generos'])
            );
        }

        $query = User::query()
            ->colaboradoresDeEmpresa($empresa->id)
            ->with(['colaborador.region', 'colaborador.ubicacion', 'colaborador.departamento', 'colaborador.area', 'colaborador.puesto']);

        return ColaboradoresFiltroAdminService::aplicarFiltrosFormulario($query, $data);
    }

    /**
     * Alinea valores de género del filtro con la columna `colaboradores.genero` (M, F, OTRO) y compatibiliza etiquetas legadas.
     *
     * @param  array<int, string>  $generos
     * @return array<int, string>
     */
    private function normalizarGenerosFiltroParaFicha(array $generos): array
    {
        $map = [
            'Masculino' => 'M',
            'Femenino' => 'F',
            'Indeterminado' => 'OTRO',
            'Otro' => 'OTRO',
        ];

        $out = [];
        foreach ($generos as $g) {
            if ($g === null || $g === '') {
                continue;
            }
            $key = is_string($g) ? trim($g) : (string) $g;
            $out[] = $map[$key] ?? $key;
        }

        return array_values(array_unique($out));
    }

    /**
     * La ficha puede tener `Otro` (legacy/factory) u `OTRO` (formulario cliente); el filtro usa la clave OTRO.
     *
     * @param  array<int, string>  $generos
     * @return array<int, string>
     */
    private function expandirGeneroOtroParaConsulta(array $generos): array
    {
        $out = [];
        foreach ($generos as $g) {
            $out[] = $g;
            if ($g === 'OTRO') {
                $out[] = 'Otro';
            }
        }

        return array_values(array_unique($out));
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $empresa = $this->getRecord();
        $producto = $this->producto;
        $razon = $data['razon'] ?? 'SERVICIO NO DISPONIBLE POR EL MOMENTO';
        $saveFilters = (bool) ($data['save_filters'] ?? false);

        DB::transaction(function () use ($empresa, $producto, $data, $razon, $saveFilters): void {
            $colaboradoresQuery = $this->getColaboradoresQuery();
            $idsActivos = $colaboradoresQuery->pluck('id')->all();

            $todosConProducto = User::query()
                ->colaboradoresDeEmpresa($empresa->id)
                ->whereHas('productos', fn ($q) => $q->where('productos.id', $producto->id))
                ->pluck('id')
                ->all();

            $now = now();
            foreach ($idsActivos as $userId) {
                ColaboradorProducto::query()->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'producto_id' => $producto->id,
                    ],
                    [
                        'estado' => 'ACTIVO',
                        'razon' => null,
                        'tipo_cambio' => 'AUTOMATIC',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $idsInactivos = array_diff($todosConProducto, $idsActivos);
            foreach ($idsInactivos as $userId) {
                ColaboradorProducto::query()
                    ->where('user_id', $userId)
                    ->where('producto_id', $producto->id)
                    ->update([
                        'estado' => 'INACTIVO',
                        'razon' => $razon,
                        'tipo_cambio' => 'AUTOMATIC',
                        'updated_at' => now(),
                    ]);
            }

            if ($saveFilters) {
                FiltroProducto::query()
                    ->where('empresa_id', $empresa->getKey())
                    ->where('producto_id', $producto->getKey())
                    ->delete();

                $u = $this->normalizeFilterIds($data['ubicacion_id'] ?? []);
                $d = $this->normalizeFilterIds($data['departamento_id'] ?? []);
                $a = $this->normalizeFilterIds($data['area_id'] ?? []);
                $p = $this->normalizeFilterIds($data['puesto_id'] ?? []);
                $r = $this->normalizeFilterIds($data['region_id'] ?? []);
                $generosStr = is_array($data['generos'] ?? null) ? implode(',', $data['generos']) : (string) ($data['generos'] ?? '');
                $mesesStr = is_array($data['meses'] ?? null) ? implode(',', $data['meses']) : (string) ($data['meses'] ?? '');
                $base = [
                    'empresa_id' => $empresa->getKey(),
                    'producto_id' => $producto->getKey(),
                    'edad_desde' => (int) ($data['edad_desde'] ?? null),
                    'edad_hasta' => (int) ($data['edad_hasta'] ?? null),
                    'mes_desde' => (int) ($data['mes_desde'] ?? null),
                    'mes_hasta' => (int) ($data['mes_hasta'] ?? null),
                    'generos' => $generosStr ?: null,
                    'meses' => $mesesStr ?: null,
                    'razon' => $razon,
                ];

                $combos = collect($u)->crossJoin($d, $a, $p, $r);
                foreach ($combos->all() as $combo) {
                    FiltroProducto::create(array_merge($base, [
                        'ubicacion_id' => $combo[0] ?? null,
                        'departamento_id' => $combo[1] ?? null,
                        'area_id' => $combo[2] ?? null,
                        'puesto_id' => $combo[3] ?? null,
                        'region_id' => $combo[4] ?? null,
                    ]));
                }
            }
        });

        Notification::make()
            ->success()
            ->title('Segmentación guardada')
            ->body('Se actualizó la asignación del producto a los colaboradores.')
            ->send();

        $this->redirect(SegmentacionProductosResource::getUrl('index'));
    }
}
