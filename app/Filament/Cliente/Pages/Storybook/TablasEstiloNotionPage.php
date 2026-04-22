<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * BL: Demo de tablas estilo Notion para Storybook. Cada bloque tiene dos modos:
 * lectura (tabla compacta) y edición (Repeater::table con + y arrastre).
 * No persiste datos: todo el estado vive en la sesión Livewire de la página.
 */
class TablasEstiloNotionPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.cliente.pages.storybook.tablas-estilo-notion';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Tablas tipo Notion';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $title = 'Tablas estilo Notion / Airtable';

    protected static ?int $navigationSort = 12;

    /** @var array<int, array{concepto: string, importe: string}> */
    public array $lineasSimples = [];

    /** @var array<int, array{titulo: string, nivel: string}> */
    public array $prioridades = [];

    /** @var array<int, array{hecho: bool, detalle: string}> */
    public array $checklist = [];

    /** @var array{lineas_simples: array<mixed>} */
    public array $lineasData = ['lineas_simples' => []];

    /** @var array{prioridades: array<mixed>} */
    public array $prioridadesData = ['prioridades' => []];

    /** @var array{checklist: array<mixed>} */
    public array $checklistData = ['checklist' => []];

    public bool $editandoLineas = false;

    public bool $editandoPrioridades = false;

    public bool $editandoChecklist = false;

    public function mount(): void
    {
        $this->lineasSimples = [
            ['concepto' => 'Material de oficina', 'importe' => '1200.00'],
            ['concepto' => 'Capacitación interna', 'importe' => '4500.50'],
        ];

        $this->prioridades = [
            ['titulo' => 'Revisar políticas de vacaciones', 'nivel' => 'alta'],
            ['titulo' => 'Actualizar organigrama', 'nivel' => 'media'],
        ];

        $this->checklist = [
            ['hecho' => true, 'detalle' => 'Definir alcance con stakeholders'],
            ['hecho' => false, 'detalle' => 'Validar datos de prueba'],
        ];
    }

    public function lineasForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('lineas_simples')
                    ->label('')
                    ->table([
                        TableColumn::make('Concepto')->markAsRequired(),
                        TableColumn::make('Importe (MXN)')->markAsRequired(),
                    ])
                    ->schema([
                        TextInput::make('concepto')
                            ->hiddenLabel()
                            ->placeholder('Ej. Viáticos Q1')
                            ->required()
                            ->maxLength(160),
                        TextInput::make('importe')
                            ->hiddenLabel()
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('0.00')
                            ->required(),
                    ])
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Añadir fila')
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ])
            ->statePath('lineasData');
    }

    public function prioridadesForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('prioridades')
                    ->label('')
                    ->table([
                        TableColumn::make('Título')->markAsRequired(),
                        TableColumn::make('Prioridad')->markAsRequired(),
                    ])
                    ->schema([
                        TextInput::make('titulo')
                            ->hiddenLabel()
                            ->maxLength(200)
                            ->required(),
                        Select::make('nivel')
                            ->hiddenLabel()
                            ->options([
                                'baja' => 'Baja',
                                'media' => 'Media',
                                'alta' => 'Alta',
                            ])
                            ->required(),
                    ])
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Añadir fila')
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ])
            ->statePath('prioridadesData');
    }

    public function checklistForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('checklist')
                    ->label('')
                    ->table([
                        TableColumn::make('Hecho')->width('6rem'),
                        TableColumn::make('Detalle')->markAsRequired(),
                    ])
                    ->schema([
                        Toggle::make('hecho')
                            ->hiddenLabel(),
                        TextInput::make('detalle')
                            ->hiddenLabel()
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Añadir ítem')
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ])
            ->statePath('checklistData');
    }

    public function editarLineas(): void
    {
        $this->lineasData = ['lineas_simples' => $this->lineasSimples];
        $this->lineasForm->fill($this->lineasData);
        $this->editandoLineas = true;
    }

    public function cancelarLineas(): void
    {
        $this->editandoLineas = false;
    }

    public function guardarLineas(): void
    {
        $estado = $this->lineasForm->getState();
        $this->lineasSimples = array_values($estado['lineas_simples'] ?? []);
        $this->editandoLineas = false;
    }

    public function editarPrioridades(): void
    {
        $this->prioridadesData = ['prioridades' => $this->prioridades];
        $this->prioridadesForm->fill($this->prioridadesData);
        $this->editandoPrioridades = true;
    }

    public function cancelarPrioridades(): void
    {
        $this->editandoPrioridades = false;
    }

    public function guardarPrioridades(): void
    {
        $estado = $this->prioridadesForm->getState();
        $this->prioridades = array_values($estado['prioridades'] ?? []);
        $this->editandoPrioridades = false;
    }

    public function editarChecklist(): void
    {
        $this->checklistData = ['checklist' => $this->checklist];
        $this->checklistForm->fill($this->checklistData);
        $this->editandoChecklist = true;
    }

    public function cancelarChecklist(): void
    {
        $this->editandoChecklist = false;
    }

    public function guardarChecklist(): void
    {
        $estado = $this->checklistForm->getState();
        $this->checklist = array_values($estado['checklist'] ?? []);
        $this->editandoChecklist = false;
    }

    public static function nivelBadgeClasses(string $nivel): string
    {
        return match ($nivel) {
            'alta' => 'bg-red-100 text-red-700',
            'media' => 'bg-amber-100 text-amber-700',
            'baja' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public static function nivelEtiqueta(string $nivel): string
    {
        return match ($nivel) {
            'alta' => 'Alta',
            'media' => 'Media',
            'baja' => 'Baja',
            default => $nivel,
        };
    }
}
