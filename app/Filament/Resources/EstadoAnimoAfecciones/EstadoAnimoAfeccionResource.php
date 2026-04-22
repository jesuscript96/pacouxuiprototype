<?php

namespace App\Filament\Resources\EstadoAnimoAfecciones;

use App\Filament\Resources\EstadoAnimoAfecciones\Pages\CreateEstadoAnimoAfeccion;
use App\Filament\Resources\EstadoAnimoAfecciones\Pages\EditEstadoAnimoAfeccion;
use App\Filament\Resources\EstadoAnimoAfecciones\Pages\ListEstadoAnimoAfecciones;
use App\Filament\Resources\EstadoAnimoAfecciones\Pages\ViewEstadoAnimoAfeccion;
use App\Filament\Resources\EstadoAnimoAfecciones\Schemas\EstadoAnimoAfeccionForm;
use App\Filament\Resources\EstadoAnimoAfecciones\Tables\EstadoAnimoAfeccionesTable;
use App\Models\EstadoAnimoAfeccion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EstadoAnimoAfeccionResource extends Resource
{
    protected static ?string $model = EstadoAnimoAfeccion::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'E. de Ánimo - Afecciones';

    protected static ?string $pluralModelLabel = 'Estados de Ánimo - Afecciones';

    public static function form(Schema $schema): Schema
    {
        return EstadoAnimoAfeccionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EstadoAnimoAfeccionesTable::configure($table);
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
            'index' => ListEstadoAnimoAfecciones::route('/'),
            'create' => CreateEstadoAnimoAfeccion::route('/create'),
            'view' => ViewEstadoAnimoAfeccion::route('/{record}'),
            'edit' => EditEstadoAnimoAfeccion::route('/{record}/edit'),
        ];
    }
}
