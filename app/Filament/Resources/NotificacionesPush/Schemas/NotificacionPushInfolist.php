<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Schemas;

use App\Enums\EstadoNotificacionPush;
use App\Models\NotificacionPush;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificacionPushInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Empresa')
                    ->schema([
                        TextEntry::make('empresa.nombre')
                            ->label('Empresa')
                            ->placeholder('—'),
                    ])
                    ->columns(1),

                Section::make('Contenido')
                    ->schema([
                        TextEntry::make('titulo')
                            ->label('Título'),
                        TextEntry::make('mensaje')
                            ->label('Mensaje')
                            ->columnSpanFull(),
                        TextEntry::make('url')
                            ->label('URL')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Estado y métricas')
                    ->schema([
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (?EstadoNotificacionPush $state): ?string => $state?->getLabel()),
                        TextEntry::make('total_destinatarios')
                            ->label('Destinatarios')
                            ->numeric(),
                        TextEntry::make('total_enviados')
                            ->label('Enviados')
                            ->numeric(),
                        TextEntry::make('total_fallidos')
                            ->label('Fallidos')
                            ->numeric(),
                        TextEntry::make('programada_para')
                            ->label('Programada para')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('enviada_at')
                            ->label('Enviada')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Estadísticas de lectura')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('estadisticas.enviados')
                                    ->label('Enviados')
                                    ->state(fn (NotificacionPush $record): int => $record->getEstadisticasLectura()['enviados'])
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('estadisticas.leidas')
                                    ->label('Leídas')
                                    ->state(fn (NotificacionPush $record): int => $record->getEstadisticasLectura()['leidas'])
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('estadisticas.no_leidas')
                                    ->label('No leídas')
                                    ->state(fn (NotificacionPush $record): int => $record->getEstadisticasLectura()['no_leidas'])
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('estadisticas.porcentaje')
                                    ->label('% lectura')
                                    ->state(fn (NotificacionPush $record): string => $record->getEstadisticasLectura()['porcentaje_lectura'].'%')
                                    ->badge()
                                    ->color(function (NotificacionPush $record): string {
                                        $pct = $record->getEstadisticasLectura()['porcentaje_lectura'];

                                        return match (true) {
                                            $pct >= 80 => 'success',
                                            $pct >= 50 => 'warning',
                                            default => 'danger',
                                        };
                                    }),
                            ]),
                    ])
                    ->visible(fn (?NotificacionPush $record): bool => $record?->estado === EstadoNotificacionPush::ENVIADA),

                Section::make('Auditoría')
                    ->schema([
                        TextEntry::make('creadoPor.name')
                            ->label('Creado por')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
