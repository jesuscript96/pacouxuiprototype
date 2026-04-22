<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Widgets;

use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DestinatariosWidget extends TableWidget
{
    public ?NotificacionPush $record = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Destinatarios')
            ->query(
                NotificacionPushDestinatario::query()
                    ->where('notificacion_push_id', $this->record?->id ?? 0)
                    ->with(['user.colaborador.ubicacion', 'user.colaborador.puesto'])
            )
            ->columns([
                TextColumn::make('user.colaborador.nombre_completo')
                    ->label('Colaborador'),
                TextColumn::make('user.colaborador.ubicacion.nombre')
                    ->label('Ubicación')
                    ->toggleable(),
                TextColumn::make('user.colaborador.puesto.nombre')
                    ->label('Puesto')
                    ->toggleable(),
                TextColumn::make('enviado')
                    ->label('Enviado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                TextColumn::make('estado_lectura')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LEIDA' => 'success',
                        'NO_LEIDA' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'LEIDA' => 'Leída',
                        'NO_LEIDA' => 'No leída',
                        default => $state,
                    }),
                TextColumn::make('enviado_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(),
                TextColumn::make('leida_at')
                    ->label('Leída')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('estado_lectura')
                    ->label('Estado de lectura')
                    ->options([
                        'NO_LEIDA' => 'No leída',
                        'LEIDA' => 'Leída',
                    ]),
                TernaryFilter::make('enviado')
                    ->label('Enviado'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50]);
    }
}
