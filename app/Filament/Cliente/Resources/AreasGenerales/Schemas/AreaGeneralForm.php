<?php

namespace App\Filament\Cliente\Resources\AreasGenerales\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AreaGeneralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del área general')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'max' => 'El nombre es demasiado largo. El máximo permitido es de 255 caracteres.',
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
