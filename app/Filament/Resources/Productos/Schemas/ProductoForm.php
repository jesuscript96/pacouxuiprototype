<?php

namespace App\Filament\Resources\Productos\Schemas;

use App\Models\Producto;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->schema([
                        TextInput::make('nombre')
                            ->unique(Producto::class)
                            ->maxLength(150)
                            ->required(),
                        Textarea::make('descripcion')
                            ->required()
                            ->rows(4),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
