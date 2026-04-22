<?php

namespace App\Filament\Resources\GestionCarruseles\Tables;

use App\Filament\Resources\GestionCarruseles\GestionCarruselesResource;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GestionCarruselesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['industria', 'subindustria', 'productos'])->orderByDesc('id'))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industria.nombre')
                    ->label('Industria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            Industria::select('nombre')->whereColumn('industrias.id', 'empresas.industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('subindustria.nombre')
                    ->label('Subindustria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            Subindustria::select('nombre')->whereColumn('sub_industrias.id', 'empresas.sub_industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('productos.nombre')
                //     ->label('Productos')
                //     ->badge()
                //     ->separator(', ')
                //     ->wrap(),
            ])
            ->recordActions([
                Action::make('gestionar_carrusel')
                    ->label('Editar Carrusel')
                    ->icon('heroicon-o-photo')
                    ->url(fn (Empresa $record): string => GestionCarruselesResource::getUrl('carrusel', ['record' => $record])),
            ]);
    }
}
