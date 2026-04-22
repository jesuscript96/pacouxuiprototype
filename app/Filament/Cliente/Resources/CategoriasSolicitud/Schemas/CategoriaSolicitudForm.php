<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoriaSolicitudForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Categoría')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id)
                            ->dehydrated()
                            ->required(),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(191),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
