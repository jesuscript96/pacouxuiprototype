<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud;

use App\Filament\Cliente\Resources\CategoriasSolicitud\Pages\CreateCategoriaSolicitud;
use App\Filament\Cliente\Resources\CategoriasSolicitud\Pages\EditCategoriaSolicitud;
use App\Filament\Cliente\Resources\CategoriasSolicitud\Pages\ListCategoriasSolicitud;
use App\Filament\Cliente\Resources\CategoriasSolicitud\Pages\ViewCategoriaSolicitud;
use App\Filament\Cliente\Resources\CategoriasSolicitud\Schemas\CategoriaSolicitudForm;
use App\Filament\Cliente\Resources\CategoriasSolicitud\Tables\CategoriasSolicitudTable;
use App\Models\CategoriaSolicitud;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CategoriaSolicitudResource extends Resource
{
    protected static ?string $model = CategoriaSolicitud::class;

    protected static ?string $slug = 'categorias_solicitudes';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categoría de solicitudes';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    public static function form(Schema $schema): Schema
    {
        return CategoriaSolicitudForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriasSolicitudTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoriasSolicitud::route('/'),
            'create' => CreateCategoriaSolicitud::route('/create'),
            'view' => ViewCategoriaSolicitud::route('/{record}'),
            'edit' => EditCategoriaSolicitud::route('/{record}/edit'),
        ];
    }

    /**
     * BL: igual que legacy — lista categorías de la empresa + catálogo global (empresa_id NULL).
     */
    public static function getEloquentQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->id;
        $query = parent::getEloquentQuery();

        if ($tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($tenantId): void {
            $q->where('empresa_id', $tenantId)->orWhereNull('empresa_id');
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
        return (bool) auth()->user()?->can('viewAny', CategoriaSolicitud::class);
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('create', CategoriaSolicitud::class);
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
        return (bool) auth()->user()?->can('deleteAny', CategoriaSolicitud::class);
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('forceDelete', $record);
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('forceDeleteAny', CategoriaSolicitud::class);
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('restore', $record);
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('restoreAny', CategoriaSolicitud::class);
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('replicate', $record);
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('reorder', CategoriaSolicitud::class);
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('view', $record);
    }
}
