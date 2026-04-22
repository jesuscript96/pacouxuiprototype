<?php

namespace App\Filament\Cliente\Resources\Vacantes\RelationManagers;

use App\Filament\Cliente\Resources\Candidatos\CandidatoReclutamientoResource;
use App\Models\CandidatoReclutamiento;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CandidatosRelationManager extends RelationManager
{
    protected static string $relationship = 'candidatos';

    protected static ?string $title = 'Candidatos';

    protected static ?string $modelLabel = 'Candidato';

    protected static ?string $pluralModelLabel = 'Candidatos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre_completo')
            ->columns([
                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(['nombre_completo', 'curp', 'email']),

                TextColumn::make('curp')
                    ->label('CURP')
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estatus')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn (CandidatoReclutamiento $record): string => $record->colorEstatus()),

                TextColumn::make('evaluacion_cv')
                    ->label('Evaluación CV')
                    ->formatStateUsing(fn ($state): string => $state !== null ? "{$state}/10" : '—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Fecha postulación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estatus')
                    ->options(
                        collect(CandidatoReclutamiento::estatusDisponibles())
                            ->mapWithKeys(fn (string $e): array => [$e => $e])
                            ->all()
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('verDetalle')
                    ->label('Ver detalle')
                    ->icon('heroicon-o-eye')
                    ->url(fn (CandidatoReclutamiento $record): string => CandidatoReclutamientoResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
