<?php

namespace App\Filament\Resources\CentroCostos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CentroCostoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->schema([

                        Select::make('servicio')
                            ->label('Servicio')
                            ->options([
                                'BELVO' => 'BELVO',
                                'EMIDA' => 'EMIDA',
                                'STP' => 'STP',
                            ])
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('cuenta_bancaria', null);
                                $set('terminal_id_tae', null);
                                $set('terminal_id_ps', null);
                                $set('clerk_id_tae', null);
                                $set('clerk_id_ps', null);
                                $set('key_id', null);
                                $set('secret_key', null);
                            }),

                        TextInput::make('nombre')
                            ->required()
                            ->label('Nombre del centro de costos'),

                        // BELVO
                        TextInput::make('key_id')
                            ->label('Clave de acceso (Key ID)')
                            ->autocomplete('off')
                            ->required(fn (Get $get) => $get('servicio') === 'BELVO')
                            ->visible(fn (Get $get) => $get('servicio') === 'BELVO'),

                        TextInput::make('secret_key')
                            ->label('Clave secreta (Secret Key)')
                            ->password()
                            ->revealable()
                            ->copyable()
                            ->required(fn (Get $get) => $get('servicio') === 'BELVO')
                            ->visible(fn (Get $get) => $get('servicio') === 'BELVO'),

                        // EMIDA
                        TextInput::make('terminal_id_tae')
                            ->label('Terminal de Recargas')
                            ->required(fn (Get $get) => $get('servicio') === 'EMIDA')
                            ->visible(fn (Get $get) => $get('servicio') === 'EMIDA'),

                        TextInput::make('terminal_id_ps')
                            ->label('Terminal de Pago de Servicios')
                            ->required(fn (Get $get) => $get('servicio') === 'EMIDA')
                            ->visible(fn (Get $get) => $get('servicio') === 'EMIDA'),

                        TextInput::make('clerk_id_tae')
                            ->label('Operador de recargas')
                            ->required(fn (Get $get) => $get('servicio') === 'EMIDA')
                            ->visible(fn (Get $get) => $get('servicio') === 'EMIDA'),

                        TextInput::make('clerk_id_ps')
                            ->label('Operador de pago de servicios')
                            ->required(fn (Get $get) => $get('servicio') === 'EMIDA')
                            ->visible(fn (Get $get) => $get('servicio') === 'EMIDA'),

                        // STP
                        TextInput::make('cuenta_bancaria')
                            ->label('Cuenta bancaria')
                            ->numeric()
                            ->required(fn (Get $get) => $get('servicio') === 'STP')
                            ->visible(fn (Get $get) => $get('servicio') === 'STP'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
