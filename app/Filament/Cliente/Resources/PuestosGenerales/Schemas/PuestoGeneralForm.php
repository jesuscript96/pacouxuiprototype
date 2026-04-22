<?php

namespace App\Filament\Cliente\Resources\PuestosGenerales\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PuestoGeneralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del puesto general')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
