<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportesInternos\Pages;

use App\Filament\Resources\ReportesInternos\ReporteInternoResource;
use App\Http\Controllers\DescargarReporteCierreMesController;
use App\Models\Empresa;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class GenerarReporteCierreMes extends Page
{
    protected static string $resource = ReporteInternoResource::class;

    /** @var array<string, mixed> */
    public array $data = [];

    private const array MESES = [
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
    ];

    public function mount(): void
    {
        $this->form->fill([
            'empresa_id' => null,
            'ubicacion_ids' => [],
            'anios' => [],
            'meses' => [],
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Reportes internos';
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
                Section::make('Genera un reporte detallado de transacciones realizadas por mes.')
                    ->schema([
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->options(fn (): array => Empresa::query()->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('ubicacion_ids', [])),
                        Select::make('ubicacion_ids')
                            ->label('Ubicaciones')
                            ->options(function (Get $get): array {
                                $empresaId = $get('empresa_id');
                                if (! $empresaId) {
                                    return [];
                                }

                                return Empresa::query()->find($empresaId)?->ubicaciones()->orderBy('nombre')->pluck('nombre', 'id')->all() ?? [];
                            })
                            ->multiple()
                            ->searchable()
                            ->placeholder('Todas las ubicaciones'),
                        Select::make('anios')
                            ->label('Años')
                            ->options(fn (): array => array_combine(
                                range(2020, now()->year + 1),
                                range(2020, now()->year + 1),
                            ) ?: [])
                            ->multiple()
                            ->placeholder('Todos los años'),
                        Select::make('meses')
                            ->label('Meses')
                            ->options(self::MESES)
                            ->multiple()
                            ->placeholder('Todos los meses'),
                    ])
                    ->columns(2),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form-reporte-cierre-mes')
                    ->livewireSubmitHandler('generar')
                    ->footer([
                        Actions::make([
                            Action::make('generar')
                                ->label('Generar reporte')
                                ->submit('generar')
                                ->icon('heroicon-o-arrow-down-tray'),
                        ]),
                    ]),
            ]);
    }

    public function generar(): void
    {
        $state = $this->form->getState();
        $empresaId = (int) ($state['empresa_id'] ?? 0);
        abort_unless($empresaId > 0, 404);

        session([
            DescargarReporteCierreMesController::SESSION_KEY => [
                'empresa_id' => $empresaId,
                'ubicacion_ids' => $state['ubicacion_ids'] ?? [],
                'anios' => $state['anios'] ?? [],
                'meses' => $state['meses'] ?? [],
            ],
        ]);

        $this->redirect(route('admin.reportes-internos.descargar-cierre-mes'));
    }
}
