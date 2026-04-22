<?php

namespace App\Filament\Resources\EstadoAnimoAfecciones\Schemas;

use App\Models\EstadoAnimoAfeccion;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EstadoAnimoAfeccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la afección')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(EstadoAnimoAfeccion::class),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
