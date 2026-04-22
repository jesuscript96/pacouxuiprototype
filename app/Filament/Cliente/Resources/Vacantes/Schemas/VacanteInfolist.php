<?php

namespace App\Filament\Cliente\Resources\Vacantes\Schemas;

use App\Models\CampoFormularioVacante;
use App\Models\Vacante;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VacanteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la vacante')
                    ->schema([
                        TextEntry::make('puesto')
                            ->label('Puesto'),

                        TextEntry::make('slug')
                            ->label('Slug'),

                        TextEntry::make('creador.name')
                            ->label('Creado por')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('candidatos_count')
                            ->label('Total de candidatos')
                            ->state(fn (Vacante $record): int => $record->candidatos()->count())
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(3),

                Section::make('Requisitos')
                    ->schema([
                        TextEntry::make('requisitos')
                            ->html()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),

                Section::make('Aptitudes')
                    ->schema([
                        TextEntry::make('aptitudes')
                            ->html()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),

                Section::make('Prestaciones')
                    ->schema([
                        TextEntry::make('prestaciones')
                            ->html()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),

                Section::make('Campos del formulario')
                    ->schema([
                        RepeatableEntry::make('camposFormulario')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('etiqueta')
                                    ->label('Campo'),
                                TextEntry::make('tipo')
                                    ->label('Tipo')
                                    ->formatStateUsing(fn (?string $state): string => CampoFormularioVacante::TIPOS[$state] ?? ($state ?? '—')),
                                IconEntry::make('requerido')
                                    ->label('Requerido')
                                    ->boolean(),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible(),
            ]);
    }
}
