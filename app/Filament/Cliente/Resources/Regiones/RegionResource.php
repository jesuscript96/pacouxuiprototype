<?php

namespace App\Filament\Cliente\Resources\Regiones;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Regiones\Pages\CreateRegion;
use App\Filament\Cliente\Resources\Regiones\Pages\EditRegion;
use App\Filament\Cliente\Resources\Regiones\Pages\ListRegiones;
use App\Filament\Cliente\Resources\Regiones\Pages\ViewRegion;
use App\Filament\Cliente\Resources\Regiones\Schemas\RegionForm;
use App\Filament\Cliente\Resources\Regiones\Tables\RegionesTable;
use App\Models\Region;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static ?string $navigationLabel = 'Regiones';

    protected static ?string $modelLabel = 'Región';

    protected static ?string $pluralModelLabel = 'Regiones';

    protected static bool $isScopedToTenant = true;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CATALOGOS_COLABORADORES;

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=regiones';
    }

    public static function form(Schema $schema): Schema
    {
        return RegionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegiones::route('/'),
            'create' => CreateRegion::route('/create'),
            'view' => ViewRegion::route('/{record}'),
            'edit' => EditRegion::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Region');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:Region');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:Region');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:Region');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:Region');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:Region');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:Region');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:Region');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:Region');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:Region');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:Region');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:Region');
    }
}
