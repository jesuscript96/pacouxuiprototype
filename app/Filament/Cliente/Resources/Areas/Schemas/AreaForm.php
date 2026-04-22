<?php

namespace App\Filament\Cliente\Resources\Areas\Schemas;

use App\Models\AreaGeneral;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del área')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'max' => 'El nombre es demasiado largo. El máximo permitido es de 255 caracteres.',
                            ]),
                        Select::make('area_general_id')
                            ->label('Área general')
                            ->relationship(
                                name: 'areaGeneral',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn ($query) => $query->where('empresa_id', Filament::getTenant()?->id)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre del área general')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data): int => AreaGeneral::create([
                                'nombre' => $data['nombre'],
                                'empresa_id' => Filament::getTenant()?->id,
                            ])->getKey()),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
