<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores\Schemas;

use App\Models\BajaColaborador;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BajaColaboradorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la baja')
                    ->schema([
                        DatePicker::make('fecha_baja')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->label('Fecha de baja')
                            ->helperText('Si la fecha es futura, la baja queda programada; si es hoy o anterior, se ejecuta al guardar o al registrar.'),
                        Select::make('motivo')
                            ->required()
                            ->options(BajaColaborador::motivosDisponibles())
                            ->label('Motivo'),
                        Textarea::make('comentarios')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
