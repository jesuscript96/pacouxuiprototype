<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales;

use App\Filament\Cliente\Resources\DepartamentosGenerales\Pages\CreateDepartamentoGeneral;
use App\Filament\Cliente\Resources\DepartamentosGenerales\Pages\EditDepartamentoGeneral;
use App\Filament\Cliente\Resources\DepartamentosGenerales\Pages\ListDepartamentosGenerales;
use App\Filament\Cliente\Resources\DepartamentosGenerales\Pages\ViewDepartamentoGeneral;
use App\Filament\Cliente\Resources\DepartamentosGenerales\Schemas\DepartamentoGeneralForm;
use App\Filament\Cliente\Resources\DepartamentosGenerales\Tables\DepartamentosGeneralesTable;
use App\Models\DepartamentoGeneral;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DepartamentoGeneralResource extends Resource
{
    protected static ?string $model = DepartamentoGeneral::class;

    protected static ?string $navigationLabel = 'Departamentos generales';

    protected static ?string $pluralModelLabel = 'Departamentos generales';

    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=departamentos_generales';
    }

    public static function form(Schema $schema): Schema
    {
        return DepartamentoGeneralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartamentosGeneralesTable::configure($table);
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
            'index' => ListDepartamentosGenerales::route('/'),
            'create' => CreateDepartamentoGeneral::route('/create'),
            'view' => ViewDepartamentoGeneral::route('/{record}'),
            'edit' => EditDepartamentoGeneral::route('/{record}/edit'),
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
            ->withCount('departamentos');
    }
}
