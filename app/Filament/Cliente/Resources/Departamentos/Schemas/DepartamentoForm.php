<?php

namespace App\Filament\Cliente\Resources\Departamentos\Schemas;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartamentoForm
{
    /**
     * BL: tipo JSON `administrador` (users.tipo) puede asignar departamento a cualquier empresa;
     * el resto queda fijado al tenant del panel (sin listar otras empresas).
     */
    public static function usuarioPuedeElegirEmpresa(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->tieneRol('administrador');
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del departamento')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id)
                            ->visible(fn (): bool => ! self::usuarioPuedeElegirEmpresa()),
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Filament::getTenant()?->id)
                            ->visible(fn (): bool => self::usuarioPuedeElegirEmpresa()),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        Select::make('departamento_general_id')
                            ->label('Departamento general')
                            ->relationship('departamentoGeneral', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
