<?php

declare(strict_types=1);

namespace App\Filament\Resources\GestionEmpleados\Tables;

use App\Filament\Resources\GestionEmpleados\GestionEmpleadosResource;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GestionEmpleadosEmpresasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['industria', 'subindustria', 'productos'])
                ->whereHas('productos')
                ->orderByDesc('id'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industria.nombre')
                    ->label('Industria')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Industria::query()->select('nombre')->whereColumn('industrias.id', 'empresas.industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('subindustria.nombre')
                    ->label('Subindustria')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Subindustria::query()->select('nombre')->whereColumn('sub_industrias.id', 'empresas.sub_industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('usuarios')
                    ->label('Usuarios')
                    ->icon('heroicon-o-users')
                    ->url(fn (Empresa $record): string => GestionEmpleadosResource::getUrl('usuarios', ['record' => $record])),
            ]);
    }
}
