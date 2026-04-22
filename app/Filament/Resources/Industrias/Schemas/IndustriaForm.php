<?php

namespace App\Filament\Resources\Industrias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndustriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la industria')
                    ->schema([
                        TextInput::make('nombre')
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
