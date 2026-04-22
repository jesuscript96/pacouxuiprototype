<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Colaborador;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CumpleanosWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();
        $empresaId = $tenant instanceof Empresa ? $tenant->id : 0;

        return $table
            ->heading('🎂 Cumpleaños de '.now()->translatedFormat('F'))
            ->query(
                Colaborador::query()
                    ->porEmpresa($empresaId)
                    ->activos()
                    ->cumpleanosMes()
                    ->with('departamento')
                    ->orderByRaw('DAY(fecha_nacimiento)')
            )
            ->columns([
                TextColumn::make('nombre_completo')
                    ->label('Colaborador')
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno'])
                    ->weight('medium'),

                TextColumn::make('departamento.nombre')
                    ->label('Departamento')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('fecha_nacimiento')
                    ->label('Cumpleaños')
                    ->formatStateUsing(fn (?\Illuminate\Support\Carbon $state): string => $state ? $state->translatedFormat('d \d\e F') : '—')
                    ->badge()
                    ->color(fn (Colaborador $record): string => $record->fecha_nacimiento?->isToday() ? 'success' : 'primary'),
            ])
            ->emptyStateIcon('heroicon-o-cake')
            ->emptyStateHeading('Sin cumpleaños este mes')
            ->emptyStateDescription(null)
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
