<?php

namespace App\Filament\Cliente\Resources\Candidatos\Tables;

use App\Exports\Reclutamiento\CandidatosVacanteExport;
use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

class CandidatosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vacante.puesto')
                    ->label('Vacante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->searchable(),

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
                SelectFilter::make('vacante_id')
                    ->label('Vacante')
                    ->relationship('vacante', 'puesto')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('estatus')
                    ->options(
                        collect(CandidatoReclutamiento::estatusDisponibles())
                            ->mapWithKeys(fn (string $e): array => [$e => $e])
                            ->all()
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                Action::make('exportarExcel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Select::make('vacante_id')
                            ->label('Vacante')
                            ->options(function (): array {
                                $query = Vacante::query()->whereHas('candidatos');

                                $tenant = Filament::getTenant();
                                if ($tenant) {
                                    $query->where('empresa_id', $tenant->id);
                                }

                                return $query->pluck('puesto', 'id')->all();
                            })
                            ->required()
                            ->searchable(),
                        Select::make('estatus')
                            ->label('Filtrar por estatus')
                            ->options(
                                collect(CandidatoReclutamiento::estatusDisponibles())
                                    ->mapWithKeys(fn (string $e): array => [$e => $e])
                                    ->all()
                            )
                            ->placeholder('Todos los estatus'),
                    ])
                    ->action(function (array $data) {
                        $vacante = Vacante::findOrFail($data['vacante_id']);
                        $estatus = $data['estatus'] ?? null;

                        $nombreArchivo = sprintf(
                            'candidatos-%s-%s.xlsx',
                            Str::slug($vacante->puesto),
                            now()->format('Y-m-d'),
                        );

                        return response()->streamDownload(function () use ($vacante, $estatus): void {
                            echo (new CandidatosVacanteExport($vacante, $estatus))->raw(Excel::XLSX);
                        }, $nombreArchivo);
                    })
                    ->visible(fn (): bool => (bool) auth()->user()?->can('ViewAny:CandidatoReclutamiento')),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
