<?php

namespace App\Filament\Resources\EstadoAnimoCaracteristicas;

use App\Filament\Resources\EstadoAnimoCaracteristicas\Pages\CreateEstadoAnimoCaracteristica;
use App\Filament\Resources\EstadoAnimoCaracteristicas\Pages\EditEstadoAnimoCaracteristica;
use App\Filament\Resources\EstadoAnimoCaracteristicas\Pages\ListEstadoAnimoCaracteristicas;
use App\Filament\Resources\EstadoAnimoCaracteristicas\Pages\ViewEstadoAnimoCaracteristica;
use App\Filament\Resources\EstadoAnimoCaracteristicas\Schemas\EstadoAnimoCaracteristicaForm;
use App\Filament\Resources\EstadoAnimoCaracteristicas\Tables\EstadoAnimoCaracteristicasTable;
use App\Models\EstadoAnimoCaracteristica;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EstadoAnimoCaracteristicaResource extends Resource
{
    protected static ?string $model = EstadoAnimoCaracteristica::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'E. de Ánimo - Características';

    protected static ?string $pluralModelLabel = 'Estados de Ánimo - Características';

    protected static ?string $modelLabel = 'Estado de ánimo - Característica';

    public static function form(Schema $schema): Schema
    {
        return EstadoAnimoCaracteristicaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EstadoAnimoCaracteristicasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEstadoAnimoCaracteristicas::route('/'),
            'create' => CreateEstadoAnimoCaracteristica::route('/create'),
            'view' => ViewEstadoAnimoCaracteristica::route('/{record}'),
            'edit' => EditEstadoAnimoCaracteristica::route('/{record}/edit'),
        ];
    }
}
