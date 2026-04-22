<?php

declare(strict_types=1);

namespace App\Filament\Resources\VerificacionCuentas;

use App\Filament\Resources\VerificacionCuentas\Tables\VerificacionCuentasTable;
use App\Models\CuentaBancaria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class VerificacionCuentaResource extends Resource
{
    protected static ?string $model = CuentaBancaria::class;

    protected static ?string $slug = 'verificacion-cuentas';

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Verificación de Cuentas';

    protected static ?string $modelLabel = 'Cuenta Bancaria';

    protected static ?string $pluralModelLabel = 'Cuentas Bancarias';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function table(Table $table): Table
    {
        return VerificacionCuentasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerificacionCuentas::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['colaborador.empresa', 'banco']);
    }

    // =====================
    // Permisos
    // =====================

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('ViewAny:VerificacionCuenta') ?? false;
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('View:VerificacionCuenta') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
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
}
