<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Colaborador;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AniversariosWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();
        $empresaId = $tenant instanceof Empresa ? $tenant->id : 0;

        return $table
            ->heading('🏅 Aniversarios de '.now()->translatedFormat('F'))
            ->query(
                Colaborador::query()
                    ->porEmpresa($empresaId)
                    ->activos()
                    ->aniversariosMes()
                    ->with('departamento')
                    ->orderByRaw('DAY(fecha_ingreso)')
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

                TextColumn::make('fecha_ingreso')
                    ->label('Fecha ingreso')
                    ->formatStateUsing(fn (?\Illuminate\Support\Carbon $state): string => $state ? $state->translatedFormat('d \d\e F Y') : '—'),

                TextColumn::make('antiguedad')
                    ->label('Antigüedad')
                    ->state(fn (Colaborador $record): string => $record->fecha_ingreso
                        ? $record->fecha_ingreso->diffInYears(now()).' '.($record->fecha_ingreso->diffInYears(now()) === 1 ? 'año' : 'años')
                        : '—'
                    )
                    ->badge()
                    ->color('success'),
            ])
            ->emptyStateIcon('heroicon-o-trophy')
            ->emptyStateHeading('Sin aniversarios este mes')
            ->emptyStateDescription(null)
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
