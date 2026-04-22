<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\Schemas;

use App\Models\Empresa;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SegmentacionVozColaboradorForm
{
    public static function configure(Schema $schema): Schema
    {
        $colaboradorToggles = User::query()
            ->whereJsonContains('tipo', 'cliente')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => Toggle::make('colaborador_'.$u->id)
                ->label(trim(implode(' ', array_filter([
                    $u->name,
                    $u->apellido_paterno ?? '',
                ])))))
            ->all();

        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Información del tema')
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                Toggle::make('exclusivo_para_empresa_toggle')
                                    ->label('Exclusivo para empresa')
                                    ->default(false)
                                    ->live(),
                                Select::make('exclusivo_para_empresa')
                                    ->label('Empresa')
                                    ->options(fn () => Empresa::query()->orderBy('nombre')->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->visible(fn ($get) => (bool) $get('exclusivo_para_empresa_toggle'))
                                    ->required(fn ($get) => (bool) $get('exclusivo_para_empresa_toggle')),
                            ]),
                        Section::make('Destinatarios')
                            ->schema([
                                Toggle::make('todos_colaboradores_toggle')
                                    ->label('Todos los colaboradores')
                                    ->default(false)
                                    ->live(),
                                Grid::make()
                                    ->schema($colaboradorToggles)
                                    ->columns(2)
                                    ->visible(fn ($get) => ! ($get('todos_colaboradores_toggle') ?? true)),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
