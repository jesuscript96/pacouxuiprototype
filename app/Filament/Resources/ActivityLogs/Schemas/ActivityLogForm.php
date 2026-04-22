<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evento')
                    ->schema([
                        TextInput::make('log_name')
                            ->label('Origen')
                            ->disabled(),
                        TextInput::make('description')
                            ->label('Descripción')
                            ->disabled(),
                        TextInput::make('event')
                            ->label('Tipo de evento')
                            ->disabled(),
                        TextInput::make('created_at')
                            ->label('Fecha')
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Origen')
                    ->schema([
                        TextInput::make('subject_type')
                            ->label('Entidad afectada')
                            ->disabled(),
                        TextInput::make('subject_id')
                            ->label('Nº de registro')
                            ->disabled(),
                        TextInput::make('causer_type')
                            ->label('Tipo de autor')
                            ->disabled(),
                        TextInput::make('causer_id')
                            ->label('Nº de usuario')
                            ->disabled(),
                        TextInput::make('causer_display')
                            ->label('Realizado por')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Detalle')
                    ->schema([
                        Textarea::make('properties')
                            ->label('Detalle del cambio')
                            ->disabled()
                            ->rows(14)
                            ->formatStateUsing(function (mixed $state): string {
                                if ($state === null || $state === '') {
                                    return '';
                                }
                                if (is_string($state)) {
                                    return $state;
                                }
                                if ($state instanceof \Illuminate\Support\Collection) {
                                    $state = $state->toArray();
                                }
                                if (is_array($state)) {
                                    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                                    return $json !== false ? $json : '';
                                }

                                return '';
                            }),
                    ]),
            ]);
    }
}
