<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores\Tables;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Models\BajaColaborador;
use App\Services\ColaboradorBajaService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BajasColaboradoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('colaborador.nombre_completo')
                    ->label('Colaborador')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('colaborador', function ($q) use ($search): void {
                            $q->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('colaborador.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('fecha_baja')
                    ->label('Fecha de baja')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => BajaColaborador::motivosDisponibles()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        BajaColaborador::MOTIVO_ABANDONO => 'warning',
                        BajaColaborador::MOTIVO_DESPIDO => 'danger',
                        BajaColaborador::MOTIVO_FALLECIMIENTO => 'gray',
                        BajaColaborador::MOTIVO_RENUNCIA => 'info',
                        BajaColaborador::MOTIVO_TERMINO_CONTRATO => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        BajaColaborador::ESTADO_PROGRAMADA => 'warning',
                        BajaColaborador::ESTADO_EJECUTADA => 'success',
                        BajaColaborador::ESTADO_CANCELADA => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('departamento.nombre')
                    ->label('Departamento al momento de la baja')
                    ->toggleable(),
                TextColumn::make('puesto.nombre')
                    ->label('Puesto al momento de la baja')
                    ->toggleable(),
                TextColumn::make('ejecutada_at')
                    ->label('Ejecutada')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Registrada')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        BajaColaborador::ESTADO_PROGRAMADA => 'Programada',
                        BajaColaborador::ESTADO_EJECUTADA => 'Ejecutada',
                        BajaColaborador::ESTADO_CANCELADA => 'Cancelada',
                    ]),
                SelectFilter::make('motivo')
                    ->options(BajaColaborador::motivosDisponibles()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (BajaColaborador $record): bool => $record->esProgramada()),
                    Action::make('reingresar')
                        ->label('Reingresar')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->visible(fn (BajaColaborador $record): bool => (bool) auth()->user()?->can('Create:Colaborador')
                            && $record->puedeReingresar())
                        ->url(fn (BajaColaborador $record): string => BajaColaboradorResource::getUrl('reingresar', [
                            'tenant' => Filament::getTenant(),
                            'record' => $record,
                        ])),
                    Action::make('cancelar')
                        ->label('Cancelar baja')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Cancelar baja programada?')
                        ->modalDescription('El colaborador permanecerá activo. Esta acción marca la baja como cancelada.')
                        ->visible(fn (BajaColaborador $record): bool => $record->esProgramada())
                        ->action(function (BajaColaborador $record): void {
                            app(ColaboradorBajaService::class)->cancelarBaja($record);
                            Notification::make()
                                ->title('Baja cancelada')
                                ->success()
                                ->send();
                        }),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(true);
    }
}
