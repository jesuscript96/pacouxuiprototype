<?php

declare(strict_types=1);

namespace App\Filament\Resources\VerificacionCuentas\Tables;

use App\Enums\EstadoVerificacionCuenta;
use App\Models\CuentaBancaria;
use App\Services\VerificacionCuentas\VerificacionCuentaService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VerificacionCuentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                CuentaBancaria::query()
                    ->whereHas('colaborador', fn ($q) => $q->whereNull('deleted_at'))
            )
            ->columns([
                TextColumn::make('numero')
                    ->label('Número de cuenta')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('colaborador.nombre_completo')
                    ->label('Colaborador')
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno'])
                    ->sortable(),

                TextColumn::make('colaborador.empresa.nombre')
                    ->label('Empresa')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('banco.nombre')
                    ->label('Banco')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                IconColumn::make('enviado_verificacion')
                    ->label('Enviada')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                IconColumn::make('es_nomina')
                    ->label('Nómina')
                    ->boolean()
                    ->trueIcon('heroicon-o-banknotes')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(collect(EstadoVerificacionCuenta::cases())->mapWithKeys(
                        fn (EstadoVerificacionCuenta $case): array => [$case->value => $case->getLabel()]
                    )->all()),

                SelectFilter::make('enviado_verificacion')
                    ->label('Enviada a verificación')
                    ->options([
                        '1' => 'Enviada',
                        '0' => 'No enviada',
                    ]),

                SelectFilter::make('es_nomina')
                    ->label('Cuenta de nómina')
                    ->options([
                        '1' => 'Sí',
                        '0' => 'No',
                    ]),

                SelectFilter::make('banco_id')
                    ->label('Banco')
                    ->relationship('banco', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('validar')
                        ->label('Validar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Validar cuenta')
                        ->modalDescription(fn (CuentaBancaria $record): string => "¿Aprobar la cuenta {$record->numero}? Se marcará como verificada y de nómina.")
                        ->visible(fn (CuentaBancaria $record): bool => $record->puedeVerificarse())
                        ->action(function (CuentaBancaria $record): void {
                            try {
                                app(VerificacionCuentaService::class)->validarCuenta($record);

                                Notification::make()
                                    ->title('Cuenta validada')
                                    ->body("La cuenta {$record->numero} ha sido validada correctamente.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar cuenta')
                        ->modalDescription(fn (CuentaBancaria $record): string => "¿Rechazar la cuenta {$record->numero}?")
                        ->visible(fn (CuentaBancaria $record): bool => $record->estado->estaSinVerificar())
                        ->action(function (CuentaBancaria $record): void {
                            try {
                                app(VerificacionCuentaService::class)->rechazarCuenta($record, reenviar: false);

                                Notification::make()
                                    ->title('Cuenta rechazada')
                                    ->body("La cuenta {$record->numero} ha sido rechazada.")
                                    ->warning()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('reenviar')
                        ->label('Reenviar a verificación')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reenviar cuenta')
                        ->modalDescription(fn (CuentaBancaria $record): string => "¿Reenviar la cuenta {$record->numero} a verificación?")
                        ->visible(fn (CuentaBancaria $record): bool => $record->puedeReenviarse())
                        ->action(function (CuentaBancaria $record): void {
                            try {
                                app(VerificacionCuentaService::class)->rechazarCuenta($record, reenviar: true);

                                Notification::make()
                                    ->title('Cuenta reenviada')
                                    ->body("La cuenta {$record->numero} ha sido reenviada a verificación.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
