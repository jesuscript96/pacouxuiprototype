<?php

namespace App\Filament\Cliente\Resources\AreasGenerales;

use App\Filament\Cliente\Resources\AreasGenerales\Pages\CreateAreaGeneral;
use App\Filament\Cliente\Resources\AreasGenerales\Pages\EditAreaGeneral;
use App\Filament\Cliente\Resources\AreasGenerales\Pages\ListAreasGenerales;
use App\Filament\Cliente\Resources\AreasGenerales\Pages\ViewAreaGeneral;
use App\Filament\Cliente\Resources\AreasGenerales\Schemas\AreaGeneralForm;
use App\Filament\Cliente\Resources\AreasGenerales\Tables\AreasGeneralesTable;
use App\Models\AreaGeneral;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AreaGeneralResource extends Resource
{
    protected static ?string $model = AreaGeneral::class;

    protected static ?string $navigationLabel = 'Áreas generales';

    protected static ?string $modelLabel = 'Área general';

    protected static ?string $pluralModelLabel = 'Áreas generales';

    protected static bool $isScopedToTenant = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=areas_generales';
    }

    public static function form(Schema $schema): Schema
    {
        return AreaGeneralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AreasGeneralesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAreasGenerales::route('/'),
            'create' => CreateAreaGeneral::route('/create'),
            'view' => ViewAreaGeneral::route('/{record}'),
            'edit' => EditAreaGeneral::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('areas');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
