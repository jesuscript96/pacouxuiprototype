<?php

namespace App\Filament\Resources\Bancos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BancoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del banco')
                    ->schema([

                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Group::make()
                            ->schema([
                                TextInput::make('codigo')
                                    ->label('Código')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('comision')
                                    ->label('Comisión por intentos de cobro')
                                    ->numeric()
                                    ->required()
                                    ->prefix('%')
                                    ->minValue(0)
                                    ->maxValue(10000)
                                    ->step(0.01),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
