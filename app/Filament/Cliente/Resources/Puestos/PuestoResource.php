<?php

namespace App\Filament\Cliente\Resources\Puestos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Puestos\Pages\CreatePuesto;
use App\Filament\Cliente\Resources\Puestos\Pages\EditPuesto;
use App\Filament\Cliente\Resources\Puestos\Pages\ListPuestos;
use App\Filament\Cliente\Resources\Puestos\Pages\ViewPuesto;
use App\Filament\Cliente\Resources\Puestos\Schemas\PuestoForm;
use App\Filament\Cliente\Resources\Puestos\Tables\PuestosTable;
use App\Models\Puesto;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PuestoResource extends Resource
{
    protected static ?string $model = Puesto::class;

    protected static ?string $navigationLabel = 'Puestos';

    protected static ?string $modelLabel = 'Puesto';

    protected static ?string $pluralModelLabel = 'Puestos';

    protected static bool $isScopedToTenant = true;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CATALOGOS_COLABORADORES;

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=puestos';
    }

    public static function form(Schema $schema): Schema
    {
        return PuestoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PuestosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPuestos::route('/'),
            'create' => CreatePuesto::route('/create'),
            'view' => ViewPuesto::route('/{record}'),
            'edit' => EditPuesto::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Puesto');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:Puesto');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:Puesto');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:Puesto');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:Puesto');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:Puesto');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:Puesto');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:Puesto');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:Puesto');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:Puesto');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:Puesto');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:Puesto');
    }
}
