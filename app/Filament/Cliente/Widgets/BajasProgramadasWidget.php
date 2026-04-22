<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\BajaColaborador;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BajasProgramadasWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Bajas programadas próximas';

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();
        $empresaId = $tenant instanceof Empresa ? $tenant->id : 0;

        return $table
            ->heading('⚠️ Bajas programadas próximas')
            ->query(
                BajaColaborador::query()
                    ->deEmpresa($empresaId)
                    ->programadas()
                    ->whereDate('fecha_baja', '>=', now()->toDateString())
                    ->whereDate('fecha_baja', '<=', now()->addDays(30)->toDateString())
                    ->with(['colaborador.departamento', 'colaborador.puesto'])
                    ->orderBy('fecha_baja')
            )
            ->columns([
                TextColumn::make('colaborador.nombre_completo')
                    ->label('Colaborador')
                    ->weight('medium')
                    ->searchable(['colaboradores.nombre', 'colaboradores.apellido_paterno']),

                TextColumn::make('colaborador.departamento.nombre')
                    ->label('Departamento')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('colaborador.puesto.nombre')
                    ->label('Puesto')
                    ->placeholder('—'),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        BajaColaborador::MOTIVO_RENUNCIA => 'Renuncia',
                        BajaColaborador::MOTIVO_DESPIDO => 'Despido',
                        BajaColaborador::MOTIVO_ABANDONO => 'Abandono',
                        BajaColaborador::MOTIVO_FALLECIMIENTO => 'Fallecimiento',
                        BajaColaborador::MOTIVO_TERMINO_CONTRATO => 'Término de contrato',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        BajaColaborador::MOTIVO_RENUNCIA => 'warning',
                        BajaColaborador::MOTIVO_DESPIDO => 'danger',
                        BajaColaborador::MOTIVO_ABANDONO => 'danger',
                        BajaColaborador::MOTIVO_FALLECIMIENTO => 'gray',
                        BajaColaborador::MOTIVO_TERMINO_CONTRATO => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('fecha_baja')
                    ->label('Fecha de baja')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn (BajaColaborador $record): string => $record->fecha_baja->diffInDays(now()) <= 7 ? 'danger' : 'warning'),
            ])
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateHeading('Sin bajas programadas')
            ->emptyStateDescription(null)
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
