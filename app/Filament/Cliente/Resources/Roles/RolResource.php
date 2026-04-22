<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Roles\Pages\CreateRol;
use App\Filament\Cliente\Resources\Roles\Pages\EditRol;
use App\Filament\Cliente\Resources\Roles\Pages\ListRoles;
use App\Filament\Cliente\Resources\Roles\Schemas\RolForm;
use App\Filament\Cliente\Resources\Roles\Tables\RolesTable;
use App\Models\Empresa;
use App\Models\SpatieRole;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RolResource extends Resource
{
    protected static ?string $model = SpatieRole::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CONFIGURACION;

    protected static ?string $modelLabel = 'Rol';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?string $slug = 'roles';

    protected static ?int $navigationSort = 100;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return RolForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRol::route('/create'),
            'edit' => EditRol::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes();

        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            $query->where('company_id', $tenant->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->where('guard_name', 'web');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:SpatieRole');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:SpatieRole');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:SpatieRole');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:SpatieRole');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:SpatieRole');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:SpatieRole');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:SpatieRole');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:SpatieRole');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:SpatieRole');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:SpatieRole');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:SpatieRole');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:SpatieRole');
    }
}
