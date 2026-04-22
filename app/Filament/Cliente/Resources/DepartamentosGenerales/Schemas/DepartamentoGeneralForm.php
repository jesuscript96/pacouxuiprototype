<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartamentoGeneralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del departamento general')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
