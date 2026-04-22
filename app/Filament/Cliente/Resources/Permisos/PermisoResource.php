<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos;

use App\Filament\Cliente\Resources\Permisos\Pages\CreatePermiso;
use App\Filament\Cliente\Resources\Permisos\Pages\EditPermiso;
use App\Filament\Cliente\Resources\Permisos\Pages\ListPermisos;
use App\Filament\Cliente\Resources\Permisos\Pages\ViewPermiso;
use App\Filament\Cliente\Resources\Permisos\Schemas\PermisoForm;
use App\Filament\Cliente\Resources\Permisos\Tables\PermisosTable;
use App\Models\TipoSolicitud;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PermisoResource extends Resource
{
    protected static ?string $model = TipoSolicitud::class;

    protected static ?string $slug = 'permisos';

    protected static ?string $navigationLabel = 'Permisos';

    protected static ?string $modelLabel = 'Permiso';

    protected static ?string $pluralModelLabel = 'Permisos';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PermisoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermisosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermisos::route('/'),
            'create' => CreatePermiso::route('/create'),
            'view' => ViewPermiso::route('/{record}'),
            'edit' => EditPermiso::route('/{record}/edit'),
        ];
    }

    /**
     * BL: tipos cuya categoría es de la empresa o catálogo global (solo lectura según policy).
     */
    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;
        $query = parent::getEloquentQuery();

        if ($tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->with('categoriaSolicitud')
            ->whereHas('categoriaSolicitud', function (Builder $q) use ($tenantId): void {
                $q->whereNull('empresa_id')
                    ->orWhere('empresa_id', $tenantId);
            });
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
        return (bool) auth()->user()?->can('viewAny', TipoSolicitud::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', TipoSolicitud::class);
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('delete', $record);
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('deleteAny', TipoSolicitud::class);
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('forceDelete', $record);
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('forceDeleteAny', TipoSolicitud::class);
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('restore', $record);
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('restoreAny', TipoSolicitud::class);
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('replicate', $record);
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('reorder', TipoSolicitud::class);
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('view', $record);
    }
}
