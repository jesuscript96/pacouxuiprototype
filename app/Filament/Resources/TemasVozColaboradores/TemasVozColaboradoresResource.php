<?php

namespace App\Filament\Resources\TemasVozColaboradores;

use App\Filament\Resources\TemasVozColaboradores\Pages\ListTemasVozColaboradores;
use App\Filament\Resources\TemasVozColaboradores\Pages\VerTemasAsignados;
use App\Models\Empresa;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TemasVozColaboradoresResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Temas voz colaborador';

    protected static ?string $modelLabel = 'Tema voz colaborador';

    protected static ?string $pluralModelLabel = 'Temas voz colaborador';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('temasVozColaboradores'))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('temas_voz_colaboradores_count')
                    ->label('Temas asignados')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->numeric(),
            ])
            ->description('Listado de Empresas')
            ->recordActions([
                \Filament\Actions\Action::make('ver_temas')
                    ->label('Ver Temas Asignados')
                    ->icon('heroicon-o-queue-list')
                    ->url(fn (Empresa $record): string => static::getUrl('temas', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemasVozColaboradores::route('/'),
            'temas' => VerTemasAsignados::route('/{record}/temas'),
        ];
    }
}
