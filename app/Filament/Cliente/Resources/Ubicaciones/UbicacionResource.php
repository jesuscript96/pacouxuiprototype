<?php

namespace App\Filament\Cliente\Resources\Ubicaciones;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Ubicaciones\Pages\CreateUbicacion;
use App\Filament\Cliente\Resources\Ubicaciones\Pages\EditUbicacion;
use App\Filament\Cliente\Resources\Ubicaciones\Pages\ListUbicaciones;
use App\Filament\Cliente\Resources\Ubicaciones\Pages\ViewUbicacion;
use App\Filament\Cliente\Resources\Ubicaciones\Schemas\UbicacionForm;
use App\Filament\Cliente\Resources\Ubicaciones\Tables\UbicacionesTable;
use App\Models\Ubicacion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $navigationLabel = 'Ubicaciones';

    protected static ?string $label = 'Ubicaciones';

    protected static bool $isScopedToTenant = true;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CATALOGOS_COLABORADORES;

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=ubicaciones';
    }

    public static function form(Schema $schema): Schema
    {
        return UbicacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UbicacionesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUbicaciones::route('/'),
            'create' => CreateUbicacion::route('/create'),
            'view' => ViewUbicacion::route('/{record}'),
            'edit' => EditUbicacion::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
