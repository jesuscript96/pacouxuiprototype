<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label('Sección')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('causer.email')
                    ->label('Usuario')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
