<?php

namespace App\Filament\Cliente\Resources\Departamentos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Departamentos\Pages\CreateDepartamento;
use App\Filament\Cliente\Resources\Departamentos\Pages\EditDepartamento;
use App\Filament\Cliente\Resources\Departamentos\Pages\ListDepartamentos;
use App\Filament\Cliente\Resources\Departamentos\Pages\ViewDepartamento;
use App\Filament\Cliente\Resources\Departamentos\Schemas\DepartamentoForm;
use App\Filament\Cliente\Resources\Departamentos\Tables\DepartamentosTable;
use App\Models\Departamento;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DepartamentoResource extends Resource
{
    protected static ?string $model = Departamento::class;

    protected static ?string $navigationLabel = 'Departamentos';

    protected static bool $isScopedToTenant = true; // --> Activa el multi-tenant por modelo

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CATALOGOS_COLABORADORES;

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=departamentos';
    }

    public static function form(Schema $schema): Schema
    {
        return DepartamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartamentosTable::configure($table);
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
            'index' => ListDepartamentos::route('/'),
            'create' => CreateDepartamento::route('/create'),
            'view' => ViewDepartamento::route('/{record}'),
            'edit' => EditDepartamento::route('/{record}/edit'),
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
