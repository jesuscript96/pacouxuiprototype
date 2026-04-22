<?php

namespace App\Filament\Cliente\Resources\PuestosGenerales;

use App\Filament\Cliente\Resources\PuestosGenerales\Pages\CreatePuestoGeneral;
use App\Filament\Cliente\Resources\PuestosGenerales\Pages\EditPuestoGeneral;
use App\Filament\Cliente\Resources\PuestosGenerales\Pages\ListPuestosGenerales;
use App\Filament\Cliente\Resources\PuestosGenerales\Pages\ViewPuestoGeneral;
use App\Filament\Cliente\Resources\PuestosGenerales\Schemas\PuestoGeneralForm;
use App\Filament\Cliente\Resources\PuestosGenerales\Tables\PuestosGeneralesTable;
use App\Models\PuestoGeneral;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PuestoGeneralResource extends Resource
{
    protected static ?string $model = PuestoGeneral::class;

    protected static ?string $navigationLabel = 'Puestos generales';

    protected static ?string $modelLabel = 'Puesto general';

    protected static ?string $pluralModelLabel = 'Puestos generales';

    protected static bool $isScopedToTenant = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=puestos_generales';
    }

    public static function form(Schema $schema): Schema
    {
        return PuestoGeneralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PuestosGeneralesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPuestosGenerales::route('/'),
            'create' => CreatePuestoGeneral::route('/create'),
            'view' => ViewPuestoGeneral::route('/{record}'),
            'edit' => EditPuestoGeneral::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('puestos');
    }
}
