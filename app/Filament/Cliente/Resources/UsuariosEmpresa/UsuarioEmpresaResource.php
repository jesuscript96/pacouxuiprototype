<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\UsuariosEmpresa;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\UsuariosEmpresa\Pages\EditUsuarioEmpresa;
use App\Filament\Cliente\Resources\UsuariosEmpresa\Pages\ListUsuariosEmpresa;
use App\Filament\Cliente\Resources\UsuariosEmpresa\Schemas\UsuarioEmpresaForm;
use App\Filament\Cliente\Resources\UsuariosEmpresa\Tables\UsuariosEmpresaTable;
use App\Models\Empresa;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UsuarioEmpresaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CONFIGURACION;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $slug = 'usuarios';

    protected static ?int $navigationSort = 101;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return UsuarioEmpresaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosEmpresaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuariosEmpresa::route('/'),
            'edit' => EditUsuarioEmpresa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();
        $query = parent::getEloquentQuery();

        if (! $tenant instanceof Empresa) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->pertenecenAEmpresa($tenant->id)
            ->where(function (Builder $q): void {
                $q->whereJsonContains('tipo', 'cliente')
                    ->orWhereJsonContains('tipo', 'colaborador');
            })
            ->whereDoesntHave('roles', function (Builder $q): void {
                $q->where('name', 'super_admin');
            })
            ->with(['colaborador', 'roles']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:User');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        if (! auth()->user()?->can('Update:User')) {
            return false;
        }
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa || ! $record instanceof User) {
            return false;
        }

        return $record->perteneceAEmpresa($tenant->id);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canRestore(Model $record): bool
    {
        return false;
    }

    public static function canRestoreAny(): bool
    {
        return false;
    }

    public static function canReplicate(Model $record): bool
    {
        return false;
    }

    public static function canReorder(): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        if (! auth()->user()?->can('View:User')) {
            return false;
        }
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa || ! $record instanceof User) {
            return false;
        }

        return $record->perteneceAEmpresa($tenant->id);
    }
}
