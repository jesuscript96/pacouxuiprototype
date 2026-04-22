<?php

namespace App\Filament\Resources\CentroCostos;

use App\Filament\Resources\CentroCostos\Pages\CreateCentroCosto;
use App\Filament\Resources\CentroCostos\Pages\EditCentroCosto;
use App\Filament\Resources\CentroCostos\Pages\ListCentroCostos;
use App\Filament\Resources\CentroCostos\Pages\ViewCentroCosto;
use App\Filament\Resources\CentroCostos\Schemas\CentroCostoForm;
use App\Filament\Resources\CentroCostos\Tables\CentroCostosTable;
use App\Models\CentroCosto;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CentroCostoResource extends Resource
{
    protected static ?string $model = CentroCosto::class;

    protected static ?string $modelLabel = 'centro de costo';

    protected static ?string $pluralModelLabel = 'centros de costo';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    public static function form(Schema $schema): Schema
    {
        return CentroCostoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentroCostosTable::configure($table);
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
            'index' => ListCentroCostos::route('/'),
            'create' => CreateCentroCosto::route('/create'),
            'view' => ViewCentroCosto::route('/{record}'),
            'edit' => EditCentroCosto::route('/{record}/edit'),
        ];
    }
}
