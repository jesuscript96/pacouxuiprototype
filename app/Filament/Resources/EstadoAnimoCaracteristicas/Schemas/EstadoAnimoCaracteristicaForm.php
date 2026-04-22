<?php

namespace App\Filament\Resources\EstadoAnimoCaracteristicas\Schemas;

use App\Models\EstadoAnimoCaracteristica;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EstadoAnimoCaracteristicaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la característica')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(EstadoAnimoCaracteristica::class),
                        Select::make('lista_inicial')
                            ->label('Lista inicial')
                            ->options([
                                'normal' => 'Normal',
                                'bad' => 'Mal',
                                'very_bad' => 'Muy mal',
                                'well' => 'Bien',
                                'very_well' => 'Muy bien',
                            ])
                            ->nullable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
