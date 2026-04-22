<?php

namespace App\Filament\Cliente\Resources\Puestos\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PuestoForm
{
    public static function configure(Schema $schema): Schema
    {
        $tenantId = Filament::getTenant()?->id;

        return $schema
            ->components([
                Section::make('Información del puesto')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => $tenantId),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        Select::make('puesto_general_id')
                            ->label('Puesto general')
                            ->relationship(
                                name: 'puestoGeneral',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn ($query) => $query->where('empresa_id', $tenantId)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('ocupacion_id')
                            ->label('Ocupación')
                            ->relationship('ocupacion', 'descripcion')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('area_general_id')
                            ->label('Área general')
                            ->relationship(
                                name: 'areaGeneral',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn ($query) => $query->where('empresa_id', $tenantId)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
