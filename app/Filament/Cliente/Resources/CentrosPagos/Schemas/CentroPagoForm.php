<?php

namespace App\Filament\Cliente\Resources\CentrosPagos\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CentroPagoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del centro de pago')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('registro_patronal')
                            ->label('Registro patronal')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('direccion_imss')
                            ->label('Dirección IMSS')
                            ->hint('Subdelegación del IMSS')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
