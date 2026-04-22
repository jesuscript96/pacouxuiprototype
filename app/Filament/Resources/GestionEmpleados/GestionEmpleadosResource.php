<?php

declare(strict_types=1);

namespace App\Filament\Resources\GestionEmpleados;

use App\Filament\Resources\GestionEmpleados\Pages\FiltrarColaboradoresEmpresaUsuario;
use App\Filament\Resources\GestionEmpleados\Pages\ListGestionEmpleados;
use App\Filament\Resources\GestionEmpleados\Pages\ListUsuariosEmpresa;
use App\Filament\Resources\GestionEmpleados\Tables\GestionEmpleadosEmpresasTable;
use App\Models\Empresa;
use App\Models\User;
use BackedEnum;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class GestionEmpleadosResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationLabel = 'Gestión de Empleados';

    protected static ?string $modelLabel = 'Gestión de Empleados';

    protected static ?string $pluralModelLabel = 'Gestión de Empleados';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $slug = 'gestion-empleados';

    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return GestionEmpleadosEmpresasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGestionEmpleados::route('/'),
            'usuarios' => ListUsuariosEmpresa::route('/{record}/usuarios'),
            'filtrar' => FiltrarColaboradoresEmpresaUsuario::route('/{record}/usuarios/{usuario}/filtrar'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user instanceof User && $user->hasRole(Utils::getSuperAdminName())) {
            return true;
        }

        return (bool) auth()->user()?->can('ViewAny:FiltroColaborador');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
