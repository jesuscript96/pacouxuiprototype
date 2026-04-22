<?php

namespace App\Filament\Resources\Subindustrias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubindustriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la subindustria')
                    ->schema([
                        TextInput::make('nombre')
                            ->required(),
                        Select::make('industria_id')
                            ->relationship('industria', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
